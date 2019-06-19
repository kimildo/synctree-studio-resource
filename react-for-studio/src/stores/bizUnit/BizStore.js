import { observable, action, computed } from 'mobx';
import _ from 'lodash';
import Util from '../../library/utils/Util';
import Request from '../../library/utils/Request';
import { Promise } from 'q';

class BizStore {
    @observable bizInfo = null;
    @observable dragging = false;
    @observable asyncMode = false;
    @observable asyncIdxs = [];
    @observable activeBuild = false;

    sampleCodeTypes = null;
    varType = {};

    @observable clientTitle = 'Consumer';

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }
    @computed get isSetData() {
        return !!this.bizInfo;
    }

    @computed get isDragging() {
        return !!this.dragging;
    }

    @action
    setDragging(dragging) {
        this.dragging = dragging;
    }

    @action
    setAsyncMode() {
        this.asyncMode = this.asyncMode === false ? true : false;
        if (!this.asyncMode) {
            this.unsetAsyncIdxs();
        } else {
            Util.alertMessage(
                'STEP 1 of 2',
                '비동기할 Operator 범위를 설정합니다. 첫번째와 마지막 Operator를 선택해 주세요.'
            );
        }
    }
    @action
    unsetAsyncMode() {
        this.asyncMode = false;
        this.unsetAsyncIdxs();
    }
    @action
    setAsyncIdxs(idx, mode) {
        if (mode) {
            this.asyncIdxs.push(idx);
        } else {
            _.remove(this.asyncIdxs, function(n) {
                return n === idx;
            });
        }
        if (this.asyncIdxs.length === 2) {
            Util.confirmMessage(
                'STEP 2 of 2',
                '비동기할 Operator 범위를 설정했습니다. 완료하시겠습니까?'
            )
                .then(() => {
                    this.asyncIdxs.sort();
                    let min = _.head(this.asyncIdxs),
                        max = _.last(this.asyncIdxs),
                        valid = true;

                    _.forEach(this.bizInfo.operators, (value, key) => {
                        let k = parseInt(key);
                        // 두 binding_seq 사이의 operator에 alter, loop 있는지 체크
                        // (현재는 operator의 length type로 구분)
                        if (
                            min < k &&
                            max > k &&
                            typeof value.length !== 'undefined'
                        ) {
                            valid = false;
                            return false;
                        }
                    });
                    return valid;
                })
                .then(valid => {
                    if (!valid) {
                        Util.showSmallBox(
                            'error_message',
                            5000,
                            '비동기할 Operator 사이에 Control List item이 삽입될 수 없습니다.'
                        );
                        this.unsetAsyncIdxs();
                        // then break
                        throw new Error('validate error');
                    }
                })
                .then(() => {
                    const submitData = {
                        app_id: this.bizInfo.app_id,
                        biz_id: this.bizInfo.biz_id,
                        first_binding_seq: _.head(this.asyncIdxs),
                        last_binding_seq: _.last(this.asyncIdxs),
                    };

                    this.request
                        .post(`/console/apps/op/setAsyncRange`, submitData)
                        .then(() => {
                            this.getBizUnit(
                                this.bizInfo.app_id,
                                this.bizInfo.biz_id
                            );
                        });
                })
                .catch(() => {
                    this.unsetAsyncIdxs();
                });
        }
    }

    @action
    unsetAsync() {
        Util.confirmMessage('Warning', '비동기 세팅을 취소하시겠습니까?').then(
            () => {
                this.request
                    .post(`/console/apps/op/unsetAsyncRange`, {
                        app_id: this.bizInfo.app_id,
                        biz_id: this.bizInfo.biz_id,
                    })
                    .then(() => {
                        this.getBizUnit(
                            this.bizInfo.app_id,
                            this.bizInfo.biz_id
                        );
                    });
            }
        );
    }
    @action
    unsetAsyncIdxs() {
        this.asyncIdxs = [];
    }

    @action
    getBizUnit(app_id, bunit_id) {
        return new Promise(resolve => {
            this.request
                .get(`/console/apps/bunit/modify/${app_id}/${bunit_id}`)
                .then(res => res.data.data)
                .then(data => {
                    data.biz_info.app_name = data.selected_app.app_name;
                    this.setBizUnit(data.biz_info);
                    this.setSampleCodeTypes(data.sample_code_types);
                    this.setVarType(data.var_types);
                    this.unsetAsyncMode();
                    resolve();
                });
        });
    }

    @action
    setSampleCodeTypes(type) {
        this.sampleCodeTypes = type;
    }

    @action
    setVarType(type) {
        this.varType = type;
    }

    @computed get getSampleCodeTypes() {
        return this.sampleCodeTypes;
    }
    @computed get getVarType() {
        return this.varType;
    }

    @action
    setBizUnit(biz) {
        const asyncSeqs = biz.async_bind_seq || null;
        const ops = Object.values(biz.operators).sort(
            (a, b) => a.binding_seq - b.binding_seq
        );
        const lines = Object.values(biz.lines).sort(
            (a, b) => a.line_idx - b.line_idx
        );
        const opArr = [];
        let asyncOp = [];
        _.forEach(ops, op => {
            const index = _.findIndex(lines, { line_idx: op.target_line_idx });
            if (index !== -1) {
                if (
                    asyncSeqs &&
                    asyncSeqs[0] <= op.binding_seq &&
                    asyncSeqs[1] >= op.binding_seq
                ) {
                    //  async
                    asyncOp.push(op);
                    // check async last object
                    if (asyncSeqs[1] === op.binding_seq) {
                        opArr.push({
                            type: 'async',
                            data: asyncOp,
                        });
                        asyncOp = [];
                    }
                } else {
                    //  normal
                    opArr.push({
                        type: 'normal',
                        data: op,
                    });
                }
            } else {
                // alter
                opArr.push({
                    type: 'alter',
                    data: op,
                });
            }
        });

        this.bizInfo = biz;
        this.clientTitle = biz.actor_alias || 'Consumer';
        this.bizInfo.operators = opArr;

        if (this.bizInfo.req_method === null) {
            this.bizInfo.req_method = 'G';
        }
        this.bizInfo.app_id = parseInt(this.bizInfo.app_id);
    }

    @action
    saveBizUnit(data = null) {
        if (!data) data = this.bizInfo;
        const targetUrl = '/console/apps/bunit/modifyCallback';
        const request = data.request.map(req => {
            let p = {
                req_key: req.req_key,
                req_var_type: req.req_var_type,
                req_desc: req.req_desc,
                req_required_flag: req.req_required_flag,
            };
            if (req.req_var_type === 'JSN') {
                p.sub_parameter_format = JSON.stringify(
                    req.sub_parameter_format
                );
            }
            if (req.param_id !== 'undefined') {
                p.param_id = req.param_id;
            }
            return p;
        });

        const submitData = {
            app_id: this.bizInfo.app_id,
            biz_id: this.bizInfo.biz_id,
            biz_name: this.bizInfo.biz_name,
            biz_desc: this.bizInfo.biz_desc,
            cache_flag: this.bizInfo.cache_flag,
            cache_expire_time: this.bizInfo.cache_expire_time,
            req_method: data.req_method,
            actor_alias: this.clientTitle,
            form_data: request,

            // operators: this.bizInfo.operators,
            // lines: this.bizInfo.lines,
        };

        this.request.post(targetUrl, submitData).then(() => {
            Util.showSmallBox('success_landing');
            this.setBuildButton(true);
            // if (typeof this.bizInfo.end_point === 'undefined') {
            this.getBizUnit(this.bizInfo.app_id, this.bizInfo.biz_id);
            // }
            this.rootStore.bizOpsStore.loadBizOps(this.bizInfo.app_id);
        });
    }

    @action
    buildBizUnit() {
        const targetUrl = '/console/apps/bunit/buildCallback';
        const submitData = {
            app_id: this.bizInfo.app_id,
            biz_id: this.bizInfo.biz_id,
        };

        this.request.post(targetUrl, submitData).then(res => {
            if (!!res) {
                Util.showSmallBox('success_landing');
                this.setBuildButton(false);

                if (typeof this.bizInfo.end_point === 'undefined') {
                    this.getBizUnit(this.bizInfo.app_id, this.bizInfo.biz_id);
                }
            }
        });
    }
    @action
    setBuildButton(bool) {
        this.activeBuild = bool;
    }

    @action
    discardBizUnit() {
        return new Promise(resolve => {
            const targetUrl = '/console/apps/bunit/remove';
            const submitData = {
                app_id: this.bizInfo.app_id,
                biz_id: this.bizInfo.biz_id,
            };

            this.request.post(targetUrl, submitData).then(() => {
                this.rootStore.bizOpsStore.loadBizOps(this.bizInfo.app_id);
                resolve();
            });
        });
    }

    @action
    changeUnitName(name) {
        this.bizInfo.biz_name = name;
    }

    @action
    changeUnitDesc(desc) {
        this.bizInfo.biz_desc = desc;
    }

    @action
    setClientTitle(title) {
        this.clientTitle = title;
    }

    @action
    setLineAttr(data) {
        this.bizInfo.lines = data;
    }

    @action
    setCacheFlag(val) {
        this.bizInfo.cache_flag = !!val ? 1 : 0;
    }
    @action
    setCacheExpireTime(t) {
        this.bizInfo.cache_expire_time = parseInt(t);
    }

    @action
    setTimelineName(index, name) {
        this.bizInfo.lines[index].line_title = name;
        this.rootStore.modalStore.hideModal();
    }
    @action
    setOperatorsAttr(data) {
        this.bizInfo.operators = data;
    }

    @action
    addReqDataFormLayer() {
        this.bizInfo.request.push({
            req_key: '',
            req_var_type: '',
            req_value: '',
            req_desc: '',
        });
    }
    @action
    modifyReqDataFormLayer(index, key, val) {
        let v = val;
        try {
            if (key === 'sub_parameter_format') {
                v = JSON.parse(v);
            }
            this.bizInfo.request[index][key] = v;
        } catch (error) {
            return;
        }
    }

    @action
    removeReqDataFormLayer(index) {
        _.pullAt(this.bizInfo.request, [index]);
    }

    @action
    changeMethod(method) {
        this.bizInfo.req_method = method;
    }

    @action
    saveRequest(data) {
        this.bizInfo.request = data;
    }

    @action
    unsetBizUnit() {
        this.bizInfo = null;
        this.dragging = false;
        this.sampleCodeTypes = null;
        this.varType = {};
        this.activeBuild = false;
    }
}

export default BizStore;
