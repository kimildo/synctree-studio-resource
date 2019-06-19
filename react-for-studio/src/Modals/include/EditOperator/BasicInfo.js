import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import HeaderFormField from '../../include/common/HeaderFormField';

@inject('opStore', 'appsStore')
@observer
class BasicInfo extends Component {
    state = {
        use_cache: false,
        toggleHeader: false,
        header: [
            {
                key: '',
                value: '',
            },
        ],
    };
    checkMethodSelected(method, expect) {
        return method === expect ? true : false;
    }

    onChangeName(e) {
        this.props.opStore.changeVal('op_name', e.target.value);
    }
    onChangeNsName(e) {
        this.props.opStore.changeVal('op_ns_name', e.target.value);
    }

    onChangeDesc(e) {
        this.props.opStore.changeVal('op_desc', e.target.value);
    }

    onChangeMethod(e) {
        this.props.opStore.changeVal('method', parseInt(e.target.value));
    }

    onChangeTargetURL(e) {
        this.props.opStore.changeVal('target_url', e.target.value);
    }

    onChangeAuthTypeCode(e) {
        this.props.opStore.changeVal('auth_type_code', e.target.value);
    }
    onChangeUseCache(e) {
        this.setState({ use_cache: e.target.checked });

        this.props.opStore.changeVal('use_cache', e.target.checked);
    }
    onChangeCacheExpire(e) {
        this.props.opStore.changeVal('cache_expire', e.target.value);
    }
    toggleHeader(e) {
        this.setState({
            toggleHeader: e.target.checked,
        });
    }
    getHeaderForm = () => {
        const { header } = this.state,
            l = header.length - 1;
        // TODO : header관련 backend 개발 완료시 추가개발 진행
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

    render() {
        // const data = this.props.opStore.operator;
        const { disabled, opStore } = this.props,
            { operator } = opStore,
            { use_cache, toggleHeader } = this.state;

        // console.log('asd', operator);

        return (
            <>
                <header>
                    <strong>Basic Info</strong>
                </header>
                <fieldset>
                    <section>
                        <label className="label">
                            <span className="text-danger">*</span> Operator Name
                        </label>
                        <label className="input">
                            <input
                                type="text"
                                required="required"
                                maxLength="30"
                                autoComplete="off"
                                placeholder="Write operator name here"
                                onChange={this.onChangeName.bind(this)}
                                value={operator.op_name}
                                disabled={disabled}
                            />
                        </label>
                    </section>

                    <section>
                        <label className="label">
                            <span className="text-info">*</span> Operator
                            Namespace
                        </label>
                        <label className="input">
                            <input
                                type="text"
                                required="required"
                                maxLength="30"
                                autoComplete="off"
                                placeholder="Write operator namespace here"
                                onChange={this.onChangeNsName.bind(this)}
                                value={operator.op_ns_name}
                                disabled={disabled}
                            />
                        </label>
                    </section>

                    <section>
                        <label className="label">
                            <span className="text-info">*</span> Description
                        </label>
                        <label className="input">
                            <input
                                type="text"
                                maxLength="100"
                                autoComplete="off"
                                placeholder="Write operator description here"
                                onChange={this.onChangeDesc.bind(this)}
                                value={operator.op_desc}
                                disabled={disabled}
                            />
                        </label>
                    </section>
                    {/* <section>
                        <label className="checkbox">
                            <input
                                type="checkbox"
                                onChange={this.toggleHeader.bind(this)}
                                checked={toggleHeader}
                            />
                            <i />
                            Header
                        </label>
                    </section>
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
                                            <span className="text-info">*</span>{' '}
                                            value
                                        </label>
                                    </div>

                                    <div className="col col-2 p-r-0">
                                        <label className="label">&nbsp;</label>
                                    </div>
                                </div>
                            </section>
                            {this.getHeaderForm()}
                        </div>
                    ) : (
                        ''
                    )} */}

                    <section>
                        <label className="label">
                            <span className="text-danger">*</span> Method
                        </label>
                        <div className="inline-group">
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="op_method"
                                    value="1"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        operator.method,
                                        1
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                Secure Protocol
                            </label>
                            <label className="radio">
                                <input
                                    type="radio"
                                    name="op_method"
                                    value="2"
                                    disabled={disabled}
                                    checked={this.checkMethodSelected(
                                        operator.method,
                                        2
                                    )}
                                    onChange={this.onChangeMethod.bind(this)}
                                />
                                <i />
                                URL Scheme
                            </label>
                        </div>
                    </section>
                    <section>
                        <label className="label">
                            <span className="text-danger">*</span> Target URL
                        </label>
                        <label className="input">
                            {' '}
                            <i className="icon-prepend fa fa-globe" />
                            <input
                                type="text"
                                maxLength="100"
                                autoComplete="off"
                                required="required"
                                placeholder="http://www.naver.com"
                                disabled={disabled}
                                value={operator.target_url}
                                onChange={this.onChangeTargetURL.bind(this)}
                            />
                        </label>
                    </section>
                    <section>
                        <label className="label">
                            <span className="text-info">*</span> API auth type
                        </label>
                        <label className="select">
                            <select
                                value={operator.auth_type_code}
                                onChange={this.onChangeAuthTypeCode.bind(this)}
                                required="required">
                                <option value={0}>No Auth</option>
                                <option value={1}>Basic</option>
                                <option value={2}>Bearer</option>
                            </select>{' '}
                            <i />
                        </label>
                    </section>
                    {/* <section>
                        <label className="checkbox">
                            <input
                                type="checkbox"
                                onChange={this.onChangeUseCache.bind(this)}
                                checked={use_cache}
                            />
                            <i />
                            Use Cache
                        </label>
                    </section>
                    {use_cache ? (
                        <section>
                            <label className="input">
                                {'Expire time(minute)'}
                                <input
                                    type="number"
                                    maxLength="100"
                                    autoComplete="off"
                                    placeholder="Write expire time."
                                    required="required"
                                    disabled={disabled}
                                    value={operator.cache_expire}
                                    onChange={this.onChangeCacheExpire.bind(
                                        this
                                    )}
                                />
                            </label>
                        </section>
                    ) : (
                        ''
                    )} */}
                </fieldset>
            </>
        );
    }
}

export default BasicInfo;
