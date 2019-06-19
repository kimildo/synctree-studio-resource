import { observable, action } from 'mobx';
import _ from 'lodash';
import Request from '../../library/utils/Request';

export default class MappingStore {
    @observable mappingGetData = null;
    @observable mappingSetData = [];

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
        this.updateOpId = null;
    }

    @action
    getMappingData(opids) {
        this.request
            .post('/console/apps/bunit/getArgumentInfo', {
                app_id: this.rootStore.bizStore.bizInfo.app_id,
                biz_id: this.rootStore.bizStore.bizInfo.biz_id,
                op_idxs: opids,
            })
            .then(res => res.data.data)
            .then(data => {
                this.updateOpId = _.last(opids);
                this.setMappingData(data.params);
            });
    }
    @action
    setMappingData(params) {
        this.mappingSetData =
            params.length === 1
                ? null
                : params[1].ops.map(op => {
                      return {
                          parameter_id: op.param_id,
                          key: op.param_key,
                          relay_flag: op.relay_flag || 0,
                          argument_value: op.argument_value,
                          param_var_type: op.param_var_type,
                          relay_parameter_id: op.relay_parameter_id,
                          relay_sub_parameter_format:
                              op.relay_sub_parameter_format,
                          relay_sub_parameter_path: op.relay_sub_parameter_path,
                          relay_binding_seq: op.relay_binding_seq,
                      };
                  });

        this.mappingGetData = params[0];
    }
    @action
    changeMappingData(index, key, value) {
        this.mappingSetData[index][key] = value;
    }
    @action
    saveMappingData(bindingSeq) {
        const args = this.mappingSetData.map(row => {
            let arg = {
                parameter_id: row.parameter_id,
                relay_flag: row.relay_flag,
                argument_value: row.argument_value,
                relay_parameter_id: row.relay_parameter_id,
                relay_sub_parameter_path: row.relay_sub_parameter_path || null,
            };
            if (!!row.relay_binding_seq) {
                arg.relay_binding_seq = row.relay_binding_seq;
            }
            return arg;
        });

        this.request
            .post('/console/apps/bunit/setArgumentInfo', {
                app_id: parseInt(this.rootStore.bizStore.bizInfo.app_id),
                biz_id: this.rootStore.bizStore.bizInfo.biz_id,
                binding_seq: bindingSeq,
                op_id: this.updateOpId,
                arguments: args,
            })
            .then(() => {
                this.unsetMapping();
                this.rootStore.bizStore.setBuildButton(true);
            });
    }
    @action
    delMappingData() {}
    @action
    unsetMapping() {
        this.mappingGetData = null;
        this.mappingSetData = [];
        this.updateOpId = null;
        this.rootStore.modalStore.hideModal();
    }
}
