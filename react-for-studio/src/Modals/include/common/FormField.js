import React, { Component } from 'react';
// import { observer } from 'mobx-react';
import _ from 'lodash';
import JSONInput from 'react-json-editor-ajrm';
import locale from 'react-json-editor-ajrm/locale/en';

import Util from '../../../library/utils/Util';

class FormField extends Component {
    constructor(props) {
        super(props);
        this.index = this.props.index;
        const data = this.makeData(this.props);

        this.state = {
            key: data.key || '',
            varType: data.var_type || '',
            desc: data.desc || '',
            requiredFlag: data.required_flag,
            activeJson: data.var_type === 'JSN',
            inputJson: !!data.jsonEditing,
            validJson: !!data.json,
            jsonStr: data.json,
        };

        this.initVarType = {
            '': '--select',
            BOL: 'BOOLEAN',
            DAT: 'DATE',
            INT: 'INTEGER',
            JSN: 'JSON',
            STR: 'STRING',
        };
    }
    makeData = p => {
        const { type, data } = p;

        return {
            key: data[`${type}_key`],
            var_type: data[`${type}_var_type`],
            desc: data[`${type}_desc`],
            required_flag:
                typeof data[`${type}_required_flag`] !== 'undefined'
                    ? parseInt(data[`${type}_required_flag`])
                    : 0,
            json: data.sub_parameter_format,
            jsonEditing:
                typeof data.json_editing === 'undefined'
                    ? false
                    : data.json_editing,
        };
    };
    componentWillReceiveProps(nextProps) {
        const data = this.makeData(nextProps);

        this.setState({
            key: data.key || '',
            varType: data.var_type || '',
            desc: data.desc || '',
            activeJson: data.var_type === 'JSN',
            inputJson: !!data.jsonEditing,
            validJson: !!data.json,
            jsonStr: data.json,
        });
    }

    getReqVarType = () => {
        const returnData = [];
        const { varType } = this.state;
        const _this = this;

        _.forOwn(this.initVarType, function(value, key) {
            returnData.push(
                <option value={key} key={`getReqVarType_${key}_${value}`}>
                    {value}
                </option>
            );
        });
        return (
            <select
                value={varType}
                onChange={_this.onChangeVarType}
                required="required">
                {returnData}
            </select>
        );
    };

    onActiveJson = () => {
        // console.log('onActiveJson', this.state);
        if (!!this.state.activeJson) {
            this.setState({
                inputJson: true,
            });
            this.props.onChangeJsonEditing(this.index, true);
        }

        // this.setState({
        //     inputJson: !!this.state.activeJson,
        // });
    };

    onChangeKey = e => {
        this.setState({
            key: e.target.value,
        });
        this.props.onChangeKey(this.index, e.target.value);
    };

    onChangeDesc = e => {
        this.setState({
            desc: e.target.value,
        });
        this.props.onChangeDesc(this.index, e.target.value);
    };

    onChangeVarType = e => {
        if (e.target.value === 'JSN') {
            this.setState({
                activeJson: true,
                inputJson: true,
                varType: e.target.value,
            });
        } else {
            this.setState({
                activeJson: false,
                inputJson: false,
                validJson: false,
                varType: e.target.value,
                jsonStr: '',
            });
        }
        this.props.onChangeVarType(this.index, e.target.value);
    };
    onConfirmJson = () => {
        const { validJson, jsonStr } = this.state;
        if (!validJson) return false;
        this.props.onChangeJson(this.index, JSON.stringify(jsonStr));
        this.setState({
            inputJson: false,
        });
        this.props.onChangeJsonEditing(this.index, false);
    };
    changeJson = str => {
        try {
            const r = JSON.parse(str);
            this.setState({
                validJson: true,
                jsonStr: r,
            });
        } catch (e) {
            this.setState({
                validJson: false,
                jsonStr: str,
            });
        }
        this.props.onChangeJson(this.index, this.state.jsonStr);
    };

    onChangeJson = e => {
        const f = e.target.files[0];
        if (f.type !== 'application/json') {
            Util.showSmallBox(
                'error_message',
                1000,
                'json 타입의 파일이 아닙니다.'
            );
            return false;
        }
        const fileReader = new FileReader();
        fileReader.onloadend = e => {
            this.changeJson(fileReader.result);
        };
        fileReader.readAsText(e.target.files[0]);
    };

    onChangeTxtarea = e => {
        this.changeJson(e.target.value);
    };
    onResetJson = () => {
        this.props.onChangeJson(this.index, '');
        this.setState({
            jsonStr: '',
        });
    };

    onEditJson = edit => {
        this.setState({
            validJson: !edit.error,
            jsonStr: edit.jsObject,
        });
    };
    addReqLayer() {
        this.props.addFormField();
    }

    removeReqLayer() {
        this.props.removeFormField(this.index);
    }
    changeReqFlag = e => {
        this.setState({
            requiredFlag: e.target.checked,
        });
        this.props.onChangeReqFlag(this.index, e.target.checked ? 1 : 0);
    };

    render() {
        const { key, desc } = this.state;
        const {
            activeJson,
            validJson,
            inputJson,
            jsonStr,
            requiredFlag,
        } = this.state;
        const { len, type } = this.props;

        const activeBtn = !jsonStr;
        const defaultBtnCls = 'btn btn-default upload-btn ellipsis';
        const btnCls = activeBtn ? defaultBtnCls : `${defaultBtnCls} disabled`;
        let s = { paddingRight: 0 };
        return (
            <section className="req-data-frm">
                <div className="row">
                    <div className="col col-2">
                        <label className="input">
                            <input
                                type="text"
                                maxLength="100"
                                autoComplete="off"
                                placeholder="key"
                                className="required"
                                onChange={this.onChangeKey}
                                value={key}
                                required="required"
                            />
                        </label>
                    </div>
                    <div className="col col-2">
                        <label className="select">
                            {this.getReqVarType()} <i />
                        </label>
                    </div>

                    <div
                        className={`p-r-0 ${
                            activeJson ? 'col col-3' : 'col col-6'
                        }`}>
                        <label className="input">
                            <input
                                type="text"
                                className="input-key"
                                maxLength="100"
                                autoComplete="off"
                                onChange={this.onChangeDesc}
                                value={desc}
                                placeholder="Description"
                            />
                        </label>
                    </div>
                    {activeJson ? (
                        <div className="col col-3 p-r-0">
                            <div className="btn-group btn-group-justified">
                                <label
                                    className="btn btn-default upload-btn ellipsis"
                                    onClick={this.onActiveJson}
                                    title={
                                        !jsonStr
                                            ? 'JSON Value'
                                            : JSON.stringify(jsonStr)
                                    }>
                                    {!jsonStr
                                        ? 'JSON Value'
                                        : JSON.stringify(jsonStr)}
                                </label>
                                <label className={btnCls}>
                                    <input
                                        className="hide"
                                        type="file"
                                        disabled={!activeBtn}
                                        onChange={this.onChangeJson}
                                    />
                                    File Upload
                                </label>
                            </div>
                        </div>
                    ) : (
                        ''
                    )}
                    {type === 'req' ? (
                        <div className="col col-1 p-r-0">
                            <div className="checkbox check-required">
                                <label>
                                    <input
                                        type="checkbox"
                                        className="checkbox style-0"
                                        checked={requiredFlag ? true : false}
                                        onChange={this.changeReqFlag}
                                    />
                                    <span>Required</span>
                                </label>
                            </div>
                        </div>
                    ) : (
                        ''
                    )}

                    <div className="col col-1  p-r-0">
                        {this.index === len ? (
                            <button
                                className="btn btn-default btn-circle btn-req-plus"
                                type="button"
                                onClick={this.addReqLayer.bind(this)}>
                                <i className="glyphicon glyphicon-plus" />
                            </button>
                        ) : (
                            ''
                        )}
                        {this.index >= 0 && len > 0 ? (
                            <button
                                className="btn btn-default txt-color-red btn-circle btn-req-minus "
                                type="button"
                                onClick={this.removeReqLayer.bind(this)}>
                                <i className="glyphicon glyphicon-minus" />
                            </button>
                        ) : (
                            ''
                        )}
                    </div>
                </div>
                {inputJson ? (
                    <div className="row" style={{ marginTop: 10 }}>
                        <div className="col col-10 relative">
                            <label className="label">Json String</label>
                            <JSONInput
                                placeholder={
                                    !!jsonStr && jsonStr !== 'null'
                                        ? jsonStr
                                        : { key: 'value' }
                                }
                                reset={false}
                                theme={'dark_vscode_tribute'}
                                onChange={this.onEditJson}
                                locale={locale}
                                height="150px"
                                width="100%"
                            />
                            <p className="json-btn-ok">
                                <button
                                    type="button"
                                    className={
                                        validJson
                                            ? 'btn btn-default btn-circle '
                                            : 'btn btn-default btn-circle disabled'
                                    }
                                    onClick={this.onConfirmJson}>
                                    OK
                                </button>
                            </p>
                        </div>
                    </div>
                ) : (
                    ' '
                )}
            </section>
        );
    }
}

export default FormField;
