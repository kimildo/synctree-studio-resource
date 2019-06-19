import { observable, action } from 'mobx';
import _ from 'lodash';
import Request from '../../library/utils/Request';

export default class OpsStore {
    @observable load = false;
    @observable selected = [];
    @observable deselected = [];

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }

    @action
    getOperators() {
        this.request
            .post('/console/apps/op/getOperators', {
                app_id: this.rootStore.bizStore.bizInfo.app_id,
                biz_id: this.rootStore.bizStore.bizInfo.biz_id,
            })
            .then(res => res.data.data)
            .then(data => {
                this.setOperatorsAll(data.op);
            });
    }
    @action
    setOperatorsAll(ops) {
        const selectedOps = this.rootStore.bizStore.bizInfo.operators.map(
            op => {
                return op.data;
            }
        );
        const selectedOp = _.flatMap(Object.values(selectedOps), n =>
            n instanceof Array ? _.flatten(n) : n
        );

        const deselectedOpData = ops.filter(row => {
            return !_.find(selectedOp, { op_id: row.op_id });
        });
        const selectedOpData = ops.filter(row =>
            _.find(selectedOp, { op_id: row.op_id })
        );

        selectedOpData.sort((a, b) => a.binding_seq - b.binding_seq);
        this.load = true;

        this.setOperatorsSelected(selectedOpData);
        this.setOperatorsDeselected(deselectedOpData);
    }
    @action
    setOperatorsSelected(ops) {
        this.selected = ops;
    }

    @action
    setOperatorsDeselected(ops) {
        this.deselected = ops;
    }

    // to => bind : deselect to select, unbind : select to deselect
    @action
    moveOperatorsByIndex(type, idxs, params = {}) {
        const ops = this.rootStore.bizStore.bizInfo.operators;
        const selectedOp = [];
        _.forEach(ops, op => {
            if (op.data instanceof Array) {
                _.forEach(op.data, d => {
                    selectedOp.push(d);
                });
            } else {
                selectedOp.push(op.data);
            }
        });

        let from = this.deselected;
        let to = selectedOp;

        if (type === 'unbind') {
            from = selectedOp;
            to = this.deselected;
        }
        const pullIndex = [];

        const submitParams = idxs.map((idx, i) => {
            let obj = _.find(from, { op_id: idx }),
                objIndex = _.findIndex(from, { op_id: idx });

            let seq = obj.binding_seq,
                lastSeq = to.length > 0 ? _.last(to).binding_seq : 0;
            if (type === 'bind') {
                seq = lastSeq + (i + 1);
            }
            from[objIndex].binding_seq = seq;
            pullIndex.push(objIndex);
            return {
                op_id: obj.op_id,
                binding_seq: seq,
            };
        });

        this.setBindOperation(type, submitParams, params);
    }

    initOperatorsByType(type) {
        if (type === 'unbind') {
            this.selected = [];
        } else {
            this.deselected = [];
        }
    }

    setBindOperation(type, opsArray, params) {
        const url = `/console/apps/op/${type}Operation`;
        const { app_id, biz_id } = this.rootStore.bizStore.bizInfo;
        const p = {
            app_id: app_id,
            biz_id: biz_id,
            bind: opsArray,
            ...params,
        };
        this.request.post(url, p).then(res => {
            this.reRenderUnitFlow();
            this.rootStore.bizStore.setBuildButton(true);
            this.rootStore.modalStore.hideModal();
        });
    }

    @action
    reRenderUnitFlow() {
        const { app_id, biz_id } = this.rootStore.bizStore.bizInfo;
        this.rootStore.bizStore.getBizUnit(app_id, biz_id).then(() => {
            this.getOperators();
        });
    }

    @action
    unsetOperators() {
        this.load = false;
        this.selected = [];
        this.deselected = [];
        this.rootStore.modalStore.hideModal();
    }
}
