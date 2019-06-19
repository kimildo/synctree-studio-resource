import { observable, action } from 'mobx';
import { toJS } from 'mobx';
import _ from 'lodash';
import Request from '../../library/utils/Request';
import { Promise } from 'q';

/* global global_data */

export default class OpStore {
    @observable operator = null;

    constructor(rootStore) {
        this.rootStore = rootStore;
        this.request = Request;
    }

    @action
    getOperator(opId) {
        let params = {
            op_id: opId,
        };
        if (global_data.partner) {
            params.account_id = global_data.partner.account_id;
            params.team_id = global_data.partner.team_id;
        }

        this.request
            .post('/console/apps/op/getOperator', params)
            .then(res => res.data.data)
            .then(data => {
                this.setOperator(data.op);
            });
    }
    @action
    setOperator(op) {
        this.operator = op;
        this.operator.type = 'modify';
    }

    @action
    createOperator() {
        this.operator = {
            type: 'create',
            app_id: this.rootStore.appsStore.getApps,
            op_name: '',
            op_ns_name: '',
            op_desc: '',
            method: 1,
            req_method: 'G',
            header_transfer_type_code: 1,
            auth_type_code: 0,
            target_url: '',
            request: [
                {
                    param_id: '',
                    req_key: '',
                    req_var_type: '',
                    req_desc: '',
                },
            ],
            response: [
                {
                    param_id: '',
                    res_key: '',
                    res_var_type: '',
                    res_desc: '',
                },
            ],
            api_auth_name: '',
            api_auth_key: '',
        };
    }

    @action
    unsetOperator() {
        // if (this.operator.type === 'create') {
        //     this.rootStore.modalStore.showModal({
        //         type: 'Operators',
        //         data: {
        //             type: 'getData',
        //         },
        //     });
        // } else {
        //     this.rootStore.modalStore.hideModal();
        // }
        this.rootStore.modalStore.hideModal();

        this.operator = null;
    }

    @action
    unsetOperatorByPage() {
        this.operator = null;
    }

    removeOperator(app_id, op_ids) {
        return new Promise(resolve => {
            this.request
                .post('/console/apps/op/remove', {
                    app_id: app_id,
                    ops: op_ids,
                })
                .then(res => {
                    resolve();
                });
        });
    }

    getSubParam(row, type) {
        let subParam = null;
        if (row[`${type}_var_type`] === 'JSN') {
            if (typeof row[`${type}_sub_param_format`] === 'undefined') {
                subParam =
                    row.sub_parameter_format !== null
                        ? JSON.stringify(row.sub_parameter_format)
                        : row.sub_parameter_format;
            } else {
                subParam = row[`${type}_sub_param_format`];
            }
        }

        return subParam;
    }

    @action
    saveOperator(appId) {
        return new Promise(resolve => {
            const req_param_id = [];
            const req_key = [];
            const req_var_type = [];
            const req_desc = [];
            const req_sub_param = [];
            const req_required_flag = [];

            const res_param_id = [];
            const res_key = [];
            const res_var_type = [];
            const res_desc = [];
            const res_sub_param = [];
            // const res_required_flag = [];

            this.operator.request.map(row => {
                req_param_id.push(row.param_id);
                req_key.push(row.req_key);
                req_var_type.push(row.req_var_type);
                req_desc.push(row.req_desc);
                req_required_flag.push(row.req_required_flag);
                let subParam = this.getSubParam(row, 'req');
                req_sub_param.push(subParam);
            });

            this.operator.response.map(row => {
                res_param_id.push(row.param_id);
                res_key.push(row.res_key);
                res_var_type.push(row.res_var_type);
                res_desc.push(row.res_desc);
                // res_required_flag.push(row.res_required_flag);
                let subParam = this.getSubParam(row, 'res');
                res_sub_param.push(subParam);
            });

            const params = {
                app_id: appId,
                op_id: this.operator.op_id,
                op_name: this.operator.op_name,
                op_ns_name: this.operator.op_ns_name,
                op_desc: this.operator.op_desc,
                op_method: this.operator.method,
                op_url: this.operator.target_url,
                op_api_auth_name: this.operator.api_auth_name,
                op_api_auth_key: this.operator.api_auth_key,
                auth_type_code: parseInt(this.operator.auth_type_code),
                req_method: this.operator.req_method,
                header_transfer_type_code: this.operator
                    .header_transfer_type_code,
                req_param_id: req_param_id,
                req_key: req_key,
                req_var_type: req_var_type,
                req_required_flag: req_required_flag,
                req_desc: req_desc,
                req_sub_param_format: req_sub_param,
                res_param_id: res_param_id,
                res_key: res_key,
                res_var_type: res_var_type,
                // res_required_flag: res_required_flag,
                res_desc: res_desc,
                res_sub_param_format: res_sub_param,
            };

            const url = global_data.partner
                ? '/partner/bunitDataAddCallback'
                : this.operator.type === 'create'
                ? '/console/apps/op/addCallback'
                : '/console/apps/op/modifyCallback';
            this.request.post(url, params).then(res => {
                this.unsetOperator();
                resolve();
            });
        });
    }

    @action
    addReqFormField() {
        this.operator.request.push({
            param_id: '',
            req_desc: '',
            req_key: '',
            req_required_flag: 0,
            req_var_type: '',
        });
    }

    @action
    removeReqFormField(index) {
        _.pullAt(this.operator.request, [index]);
    }

    @action
    addResFormField() {
        this.operator.response.push({
            param_id: '',
            res_desc: '',
            res_key: '',
            res_var_type: '',
        });
    }

    @action
    removeResFormField(index) {
        _.pullAt(this.operator.response, [index]);
    }

    @action
    changeVal(key, val) {
        this.operator[key] = val;
    }

    @action
    changeReqValue(index, key, value) {
        this.operator.request[index][key] = value;
    }
    @action
    changeResValue(index, key, value) {
        this.operator.response[index][key] = value;
    }

    @action
    replaceReqValue(jsonArr) {
        this.operator.request = jsonArr;
    }
    @action
    replaceResValue(jsonArr) {
        this.operator.response = jsonArr;
    }
    @action
    pushReqValue(jsonArr) {
        this.operator.request = toJS(this.operator.request).concat(jsonArr);
    }
    @action
    pushResValue(jsonArr) {
        this.operator.response = toJS(this.operator.response).concat(jsonArr);
    }
}
