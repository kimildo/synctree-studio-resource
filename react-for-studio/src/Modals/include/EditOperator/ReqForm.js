import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';

import { varType } from '../../../library/constant/CommonConst';

import FormField from '../common/FormField';
import FileUploadParams from '../common/FileUploadParams';

@inject('opStore')
@observer
class ReqForm extends Component {
    checkMethodSelected(method, expect) {
        return method === expect ? true : false;
    }
    onChangeMethod(e) {
        this.props.opStore.changeVal('req_method', e.target.value);
    }
    onChangeHeaderType(e) {
        this.props.opStore.changeVal(
            'header_transfer_type_code',
            e.target.value
        );
    }

    addReqFormField() {
        this.props.opStore.addReqFormField();
    }
    removeReqFormField(index) {
        this.props.opStore.removeReqFormField(index);
    }
    onChangeReqKey(index, value) {
        this.props.opStore.changeReqValue(index, 'req_key', value);
    }
    onChangeReqVarType(index, value) {
        this.props.opStore.changeReqValue(index, 'req_var_type', value);
    }
    onChangeReqDesc(index, value) {
        this.props.opStore.changeReqValue(index, 'req_desc', value);
    }
    onChangeReqJson(index, value) {
        this.props.opStore.changeReqValue(index, 'req_sub_param_format', value);
    }
    onChangeJsonEditing = (index, bool) => {
        this.props.opStore.changeReqValue(index, 'json_editing', bool);
    };
    onChangeReqFlag = (index, value) => {
        this.props.opStore.changeReqValue(index, 'req_required_flag', value);
    };

    updateParams(result, type = 'replace') {
        let json = result.map(r => ({
            req_key: r.key || '',
            req_var_type: !!r.var_type
                ? _.findKey(varType, item => item === r.var_type.toUpperCase())
                : '',
            req_desc: r.desc || '',
            req_required_flag: r.required_flag.toUpperCase() === 'TRUE' ? 1 : 0,
        }));

        if (type === 'replace') {
            this.props.opStore.replaceReqValue(json);
        } else {
            this.props.opStore.pushReqValue(json);
        }
    }

    renderFormField() {
        const data = this.props.opStore.operator;
        const l = data.request.length - 1;
        return data.request.map((row, i) => (
            <FormField
                data={row}
                index={i}
                len={l}
                key={`ReqFormField_${i}`}
                type={'req'}
                addFormField={this.addReqFormField.bind(this)}
                removeFormField={this.removeReqFormField.bind(this)}
                onChangeKey={this.onChangeReqKey.bind(this)}
                onChangeVarType={this.onChangeReqVarType.bind(this)}
                onChangeDesc={this.onChangeReqDesc.bind(this)}
                onChangeJson={this.onChangeReqJson.bind(this)}
                onChangeJsonEditing={this.onChangeJsonEditing}
                onChangeReqFlag={this.onChangeReqFlag}
            />
        ));
    }

    render() {
        const data = this.props.opStore.operator;
        const { disabled } = this.props;

        return (
            <>
                <header>
                    <strong>Request Form-Data</strong>
                </header>

                <fieldset className="fieldset-input">
                    <section>
                        <label className="label">
                            <span className="text-danger">*</span> Request
                            Method
                        </label>
                        <div className="inline-group">
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="req_method"
                                    value="G"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        data.req_method,
                                        'G'
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                GET(query)
                            </label>

                            <label className="radio">
                                <input
                                    type="radio"
                                    name="req_method"
                                    value="P"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        data.req_method,
                                        'P'
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                POST
                            </label>
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="req_method"
                                    value="C"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        data.req_method,
                                        'C'
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                GET(Clean URL)
                            </label>
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="req_method"
                                    value="U"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        data.req_method,
                                        'U'
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                PUT
                            </label>
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="req_method"
                                    value="D"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        data.req_method,
                                        'D'
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                DELETE
                            </label>
                        </div>
                    </section>

                    <section>
                        <label className="label">
                            <span className="text-danger">*</span> Request
                            Header Contents-Type
                        </label>
                        <label className="select">
                            <select
                                value={data.header_transfer_type_code}
                                onChange={this.onChangeHeaderType.bind(this)}
                                required="required">
                                <option value={1}>
                                    form-data (non-header)
                                </option>
                                <option value={2}>application/json</option>
                                <option value={3}>
                                    application/x-www-form-urlencoded
                                </option>
                                <option value={4}>application/xml</option>
                            </select>{' '}
                            <i />
                        </label>
                    </section>
                    {this.renderFormField()}
                    <FileUploadParams
                        updateParams={this.updateParams.bind(this)}
                        params={data.request}
                        type="req"
                    />
                </fieldset>
            </>
        );
    }
}

export default ReqForm;
