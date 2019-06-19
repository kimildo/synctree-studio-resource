import { observable, action, computed } from 'mobx';
import _ from 'lodash';
import Request from '../library/utils/Request';

export default class AlterStore {
    @observable alterGetData = null;
    @observable controlOperator = null;
    @observable alterSetData = {
        opt1: '',
        opt2: '',
        json: null,
        jsonPath: '',
        auth: '',
        desc: '',
        cases: [
            {
                controll: '',
                value: '',
                opId: '',
            },
        ],
    };

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
        if (
            !!document.querySelector('#app') &&
            !!this.rootStore.userInfoStore.isLoggedIn
        ) {
            this.getControllOperators();
        }
    }

    @computed get getData() {
        return this.alterGetData;
    }

    @computed get setData() {
        return this.alterSetData;
    }

    @action
    getControllOperators() {
        this.request
            .get('/console/apps/op/getControllOperators')
            .then(res => res.data.data)
            .then(data => {
                this.setControlOperator(data);
            });
    }

    @action
    setControlOperator(data) {
        this.controlOperator = data;
    }
    @action
    getAlterData(opid) {
        this.alterGetData = null;
        return new Promise(resolve => {
            this.request
                .post('/console/apps/bunit/getArgumentInfo', {
                    app_id: this.rootStore.bizStore.bizInfo.app_id,
                    biz_id: this.rootStore.bizStore.bizInfo.biz_id,
                    op_idxs: !opid ? [0] : [opid, 0],
                })
                .then(res => res.data.data)
                .then(data => {
                    this.setAlterData(data.params);
                    resolve();
                });
        });
    }
    @action
    setAlterData(params) {
        this.alterGetData = params[0];
    }
    @action
    setAlterSetData(base, ops) {
        // parameter_id로 비즈 옵스 찾기
        _.forEach(this.alterGetData, (value, key) => {
            if (!!_.find(value, { param_id: base.parameter_id })) {
                this.alterSetData.opt1 = key;
            }
        });
        this.alterSetData.opt2 = base.parameter_id;
        this.alterSetData.json = JSON.parse(base.sub_parameter_format);
        this.alterSetData.jsonPath = base.sub_parameter_path;
        this.alterSetData.desc = base.control_alt_description;
        this.alterSetData.id = base.control_alt_id;
        this.alterSetData.cases = ops.map(op => {
            return {
                controll: op.controll_info.operator || '',
                value: op.controll_info.value,
                opId: op.op_id,
            };
        });
        this.modify = true;
        console.log('setAlterSetData', this.alterSetData);
    }

    @action
    changeAlterData(key, value) {
        this.alterSetData[key] = value;
    }
    @action
    addCase() {
        this.alterSetData.cases.push({
            controll: '',
            value: '',
            opId: '',
        });
    }

    @action
    removeCase(index) {
        _.pullAt(this.alterSetData.cases, index);
    }

    @action
    changeAlterCaseData(index, key, value) {
        this.alterSetData.cases[index][key] = value;
    }
    @action
    saveAlterData() {
        const lastSelected = _.last(this.rootStore.opsStore.selected) || null;
        const lastBindingSeq = lastSelected ? lastSelected.binding_seq : 0;
        const { app_id, biz_id } = this.rootStore.bizStore.bizInfo;
        const { opt2, desc, jsonPath, auth, cases } = this.alterSetData;
        let url = '/console/apps/op/bindAlter';

        const saveData = {
            app_id: app_id,
            biz_id: biz_id,
            binding_seq: lastBindingSeq + 1,
            parameter_id: opt2,
            alt_description: desc,
            auth_keys: auth,
            sub_parameter_path: jsonPath === '' ? null : jsonPath,
            bind: [],
        };
        saveData.bind = cases.map((c, i) => {
            return {
                op_id: parseInt(c.opId),
                binding_seq: lastBindingSeq + (i + 2),
                control_operator: parseInt(c.controll),
                control_value: c.value,
            };
        });
        if (typeof this.alterSetData.id !== 'undefined') {
            saveData.control_alt_id = this.alterSetData.id;
            url = '/console/apps/op/modifyAlterCallback';
        }

        this.request.post(url, saveData).then(() => {
            this.rootStore.bizStore.getBizUnit(app_id, biz_id);
            this.unsetAlter();
        });
    }
    @action
    unbindAlter(data) {
        const { app_id, biz_id } = this.rootStore.bizStore.bizInfo;
        const params = {
            app_id: app_id,
            biz_id: biz_id,
            control_alt_id: '',
            bind: [],
        };
        params.bind = data.map(r => {
            if (params.control_alt_id === '') {
                params.control_alt_id = r.controll_info.control_id;
            }
            return {
                op_id: r.op_id,
                binding_seq: r.binding_seq,
            };
        });
        this.request.post('/console/apps/op/unbindAlter', params).then(res => {
            this.rootStore.bizStore.getBizUnit(app_id, biz_id);
        });
    }

    @action
    initAlterData() {
        this.alterSetData = {
            opt1: '',
            opt2: '',
            json: null,
            jsonPath: '',
            auth: '',
            desc: '',
            cases: [
                {
                    controll: '',
                    value: '',
                    opId: '',
                },
            ],
        };
    }
    @action
    unsetAlter() {
        this.alterGetData = null;
        this.initAlterData();
        this.rootStore.modalStore.hideModal();
    }
}
