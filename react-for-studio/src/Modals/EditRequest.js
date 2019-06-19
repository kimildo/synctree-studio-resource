import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';

import Util from '../library/utils/Util';
import { varType } from '../library/constant/CommonConst';

import RequestMethod from './include/EditRequest/RequestMethod';
import FormField from './include/common/FormField';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import FileUploadParams from './include/common/FileUploadParams';
import HeaderFormField from './include/common/HeaderFormField';
/* global global_data */

@inject('bizStore', 'modalStore')
@observer
class EditRequest extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        const { req_method, request } = this.props.bizStore.bizInfo;
        this.state = {
            req_method: req_method,
            request: request.toJS(), // observableArray => array 변환
            toggleHeader: false,
            header: [
                {
                    key: '',
                    value: '',
                },
            ],
        };
    }
    onChangeMethod(e) {
        this.setState({
            req_method: e.target.value,
        });
    }
    toggleHeader(e) {
        this.setState({
            toggleHeader: e.target.checked,
        });
    }
    onChangeHeaderKey = (index, value) => {
        this.setState(prevState => {
            prevState.header[index].key = value;
            return { header: prevState.header };
        });
    };
    onChangeHeaderValue = (index, value) => {
        this.setState(prevState => {
            prevState.header[index].value = value;
            return { header: prevState.header };
        });
    };
    addHeaderFormField = () => {
        this.setState(prevState => {
            prevState.header.push({
                key: '',
                value: '',
            });
            return { header: prevState.header };
        });
    };
    removeHeaderFormField = index => {
        this.setState(prevState => {
            _.pullAt(prevState.header, [index]);
            return { header: prevState.header };
        });
    };
    getHeaderForm = () => {
        // TODO : header관련 backend 개발 완료시 추가개발 진행
        const { header } = this.state,
            l = header.length - 1;

        return header.map((h, i) => (
            <HeaderFormField
                data={h}
                index={i}
                len={l}
                key={`getHeaderForm_${i}`}
                onChangeKey={this.onChangeHeaderKey}
                onChangevalue={this.onChangeHeaderValue}
                addFormField={this.addHeaderFormField}
                removeFormField={this.removeHeaderFormField}
            />
        ));
    };

    getReqDataForm() {
        const r = this.state.request,
            l = r.length - 1;

        return r.map((row, i) => (
            <FormField
                data={row}
                index={i}
                len={l}
                key={`ReqDataForm_${i}`}
                type={'req'}
                addFormField={this.addReqFormField}
                removeFormField={this.removeReqFormField}
                onChangeKey={this.onChangeReqKey}
                onChangeVarType={this.onChangeReqVarType}
                onChangeDesc={this.onChangeReqDesc}
                onChangeJson={this.onChangeReqJson}
                onChangeJsonEditing={this.onChangeJsonEditing}
                onChangeReqFlag={this.onChangeReqFlag}
            />
        ));
    }
    addReqFormField = () => {
        this.setState(prevState => {
            prevState.request.push({
                req_key: '',
                req_var_type: '',
                req_value: '',
                req_desc: '',
            });
            return { request: prevState.request };
        });
    };
    removeReqFormField = index => {
        this.setState(prevState => {
            _.pullAt(prevState.request, [index]);
            return { request: prevState.request };
        });
    };

    modifyData(index, key, val) {
        let v = val;
        try {
            if (key === 'sub_parameter_format') {
                v = JSON.parse(v);
            }
            this.setState(prevState => {
                prevState.request[index][key] = v;
                return { request: prevState.request };
            });
        } catch (error) {
            return;
        }
    }

    onChangeReqKey = (index, value) => {
        this.modifyData(index, 'req_key', value);
    };
    onChangeReqDesc = (index, value) => {
        this.modifyData(index, 'req_desc', value);
    };
    onChangeReqVarType = (index, value) => {
        this.modifyData(index, 'req_var_type', value);
    };
    onChangeReqDesc = (index, value) => {
        this.modifyData(index, 'req_desc', value);
    };
    onChangeReqJson = (index, value) => {
        this.modifyData(index, 'sub_parameter_format', value);
    };
    onChangeJsonEditing = (index, bool) => {
        this.modifyData(index, 'json_editing', bool);
    };
    onChangeReqFlag = (index, value) => {
        this.modifyData(index, 'req_required_flag', value);
    };

    saveRequest(e) {
        e.preventDefault();

        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.bizStore.saveBizUnit(this.state);
            this.props.modalStore.hideModal();
        }
    }

    // type = replace, push
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
            this.setState({
                request: [...json],
            });
        } else {
            this.setState(prevState => {
                prevState.request.push(...json);
                return { request: prevState.request };
            });
        }
    }

    render() {
        const { dictionary } = global_data,
            { req_method, toggleHeader, request } = this.state;
        return (
            <ModalWrapper>
                <form
                    className="modal-content"
                    ref={this.formEl}
                    onSubmit={this.saveRequest.bind(this)}>
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-exchange" /> Set Client
                        Flow Info
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body no-padding">
                            <div className="smart-form">
                                <header>
                                    <strong>Request Form-Data</strong>
                                </header>
                                <fieldset className="fieldset-input">
                                    {/* <label className="checkbox">
                                        <input
                                            type="checkbox"
                                            onChange={this.toggleHeader.bind(
                                                this
                                            )}
                                            checked={toggleHeader}
                                        />
                                        <i />
                                        Header
                                    </label>
                                    {toggleHeader ? (
                                        <div className={'req-header'}>
                                            <section className="req-data-frm m-b-0">
                                                <div className="row">
                                                    <div className="col col-2">
                                                        <label className="label">
                                                            <span className="text-danger">
                                                                *
                                                            </span>{' '}
                                                            Key
                                                        </label>
                                                    </div>

                                                    <div className="col col-8 p-r-0">
                                                        <label className="label">
                                                            <span className="text-info">
                                                                *
                                                            </span>{' '}
                                                            value
                                                        </label>
                                                    </div>

                                                    <div className="col col-2 p-r-0">
                                                        <label className="label">
                                                            &nbsp;
                                                        </label>
                                                    </div>
                                                </div>
                                            </section>
                                            {this.getHeaderForm()}
                                        </div>
                                    ) : (
                                        ''
                                    )} */}

                                    <RequestMethod
                                        reqMethod={req_method}
                                        onChangeMethod={this.onChangeMethod.bind(
                                            this
                                        )}
                                    />
                                    <section className="req-data-frm m-b-0">
                                        <div className="row">
                                            <div className="col col-2">
                                                <label className="label">
                                                    <span className="text-danger">
                                                        *
                                                    </span>{' '}
                                                    Key
                                                </label>
                                            </div>
                                            <div className="col col-2">
                                                <label className="label">
                                                    <span className="text-danger">
                                                        *
                                                    </span>{' '}
                                                    Var Type
                                                </label>
                                            </div>
                                            <div className="col col-6 p-r-0">
                                                <label className="label">
                                                    <span className="text-info">
                                                        *
                                                    </span>{' '}
                                                    Description
                                                </label>
                                            </div>

                                            <div className="col col-2 p-r-0">
                                                <label className="label">
                                                    &nbsp;
                                                </label>
                                            </div>
                                        </div>
                                    </section>
                                    {this.getReqDataForm()}
                                    <FileUploadParams
                                        updateParams={this.updateParams.bind(
                                            this
                                        )}
                                        params={request}
                                        type="req"
                                    />
                                </fieldset>
                            </div>
                        </div>
                    </div>
                    <ModalFooter>
                        <button type="submit" className="btn btn-primary">
                            <i className="fa fa-plus" />{' '}
                            {dictionary.button.save}
                        </button>
                    </ModalFooter>
                </form>
            </ModalWrapper>
        );
    }
}

export default EditRequest;
