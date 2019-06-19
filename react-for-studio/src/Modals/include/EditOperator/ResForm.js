import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';

import { varType } from '../../../library/constant/CommonConst';

import FormField from '../../include/common/FormField';
import FileUploadParams from '../common/FileUploadParams';

@inject('opStore')
@observer
class ResForm extends Component {
    constructor(props) {
        super(props);

        this.addResFormField = this.addResFormField.bind(this);
        this.removeResFormField = this.removeResFormField.bind(this);
        this.onChangeResKey = this.onChangeResKey.bind(this);
        this.onChangeResVarType = this.onChangeResVarType.bind(this);
        this.onChangeResDesc = this.onChangeResDesc.bind(this);
    }

    addResFormField() {
        this.props.opStore.addResFormField();
    }
    removeResFormField(index) {
        this.props.opStore.removeResFormField(index);
    }
    onChangeResKey(index, value) {
        this.props.opStore.changeResValue(index, 'res_key', value);
    }
    onChangeResVarType(index, value) {
        this.props.opStore.changeResValue(index, 'res_var_type', value);
    }
    onChangeResDesc(index, value) {
        this.props.opStore.changeResValue(index, 'res_desc', value);
    }
    onChangeResJson(index, value) {
        this.props.opStore.changeResValue(index, 'res_sub_param_format', value);
    }
    onChangeJsonEditing = (index, bool) => {
        this.props.opStore.changeResValue(index, 'json_editing', bool);
    };
    onChangeReqFlag = (index, value) => {
        this.props.opStore.changeReqValue(index, 'res_required_flag', value);
    };
    updateParams(result, type = 'replace') {
        console.log('ResForm updateParams :', result, type);
        let json = result.map(r => ({
            res_key: r.key || '',
            res_var_type: !!r.var_type
                ? _.findKey(varType, item => item === r.var_type.toUpperCase())
                : '',
            res_desc: r.desc || '',
            res_required_flag: r.required_flag.toUpperCase() === 'TRUE' ? 1 : 0,
        }));

        if (type === 'replace') {
            this.props.opStore.replaceResValue(json);
        } else {
            this.props.opStore.pushResValue(json);
        }
    }

    renderFormField() {
        const data = this.props.opStore.operator,
            l = data.response.length - 1;
        return data.response.map((row, i) => (
            <FormField
                data={row}
                index={i}
                len={l}
                key={`ReqFormField_${i}`}
                type={'res'}
                addFormField={this.addResFormField.bind(this)}
                removeFormField={this.removeResFormField.bind(this)}
                onChangeKey={this.onChangeResKey.bind(this)}
                onChangeVarType={this.onChangeResVarType.bind(this)}
                onChangeDesc={this.onChangeResDesc.bind(this)}
                onChangeJson={this.onChangeResJson.bind(this)}
                onChangeJsonEditing={this.onChangeJsonEditing}
                onChangeReqFlag={this.onChangeReqFlag}
            />
        ));
    }

    render() {
        return (
            <>
                <header>
                    <strong>Response Data</strong>
                </header>

                <fieldset className="fieldset-input">
                    {this.renderFormField()}
                    <FileUploadParams
                        updateParams={this.updateParams.bind(this)}
                        params={this.props.opStore.operator.response}
                        type="res"
                    />
                </fieldset>
            </>
        );
    }
}

export default ResForm;
