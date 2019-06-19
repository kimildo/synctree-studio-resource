import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Helmet } from 'react-helmet';
import Util from '../../library/utils/Util';
import Loading from '../../Loading';
/* global global_data */

@inject('navStore', 'bizStore', 'modalStore')
@observer
class UnitProperty extends Component {
    formEl = React.createRef();
    state = { loading: false };

    componentDidMount() {
        this.props.navStore.setNav(['Apps', 'biz Unit', 'Unit Property']);
        this.validData(this.props.match.params);
    }

    componentWillReceiveProps(nextProps) {
        this.validData(nextProps.match.params);
    }
    validData(params) {
        const bizInfo = this.props.bizStore,
            { app_id, bunit_id } = params;

        if (
            parseInt(app_id) !== bizInfo.app_id ||
            parseInt(bunit_id) !== bizInfo.biz_id
        ) {
            this.setState({ loading: true });
            this.props.bizStore.getBizUnit(app_id, bunit_id).then(() => {
                this.setState({ loading: false });
            });
        }
    }

    onChangeName(e) {
        this.props.bizStore.changeUnitName(e.target.value);
    }

    onChangeDesc(e) {
        this.props.bizStore.changeUnitDesc(e.target.value);
    }

    onChangeCacheFlag() {
        this.props.bizStore.setCacheFlag(
            !!this.props.bizStore.bizInfo.cache_flag ? false : true
        );
    }

    onChangeCacheExpire(e) {
        this.props.bizStore.setCacheExpireTime(e.target.value);
    }

    viewSampleSource(type) {
        this.props.modalStore.showModal({
            type: 'SampleSource',
            data: {
                type: type,
            },
        });
    }

    saveRequest(e) {
        e.preventDefault();
        let valid = Util.formCheckRequired($(this.formEl.current));
        if (valid) {
            this.props.bizStore.saveBizUnit();
        }
    }

    render() {
        const { dictionary } = global_data;
        const { bizInfo } = this.props.bizStore;
        const editable =
            bizInfo.request[0].req_desc === '' &&
            bizInfo.request[0].req_key === '' &&
            bizInfo.request[0].req_var_type === ''
                ? false
                : true;

        const documentUrl = `${location.origin}/docs/${bizInfo.app_id}/${
            bizInfo.biz_id
        }`;
        const { loading } = this.state;

        return (
            <form
                className="smart-form unit-form"
                ref={this.formEl}
                onSubmit={this.saveRequest.bind(this)}>
                <Helmet>
                    <title>Unit Property - Synctree Studio V2.0 </title>
                </Helmet>
                <header>
                    <strong> Biz Unit </strong>
                </header>
                {loading ? (
                    <Loading />
                ) : (
                    <fieldset>
                        <section>
                            <label className="label">
                                <span className="text-danger"> * </span> Name
                            </label>
                            <label className="input">
                                <i className="icon-append fa fa-question-circle" />
                                <input
                                    type="text"
                                    id="bunit_name"
                                    name="bunit_name"
                                    required="required"
                                    maxLength={30}
                                    autoComplete="off"
                                    value={bizInfo.biz_name}
                                    readOnly={!editable}
                                    onChange={this.onChangeName.bind(this)}
                                />
                                <b className="tooltip tooltip-top-right">
                                    <i className="fa fa-warning txt-color-teal" />
                                    Biz unit name
                                </b>
                            </label>
                        </section>
                        <section>
                            <label className="label"> Description </label>
                            <label className="input">
                                <i className="icon-append fa fa-question-circle" />
                                <input
                                    type="text"
                                    id="bunit_desc"
                                    name="bunit_desc"
                                    maxLength={100}
                                    autoComplete="off"
                                    onChange={this.onChangeDesc.bind(this)}
                                    value={bizInfo.biz_desc}
                                    readOnly={!editable}
                                />
                                <b className="tooltip tooltip-top-right">
                                    <i className="fa fa-warning txt-color-teal" />
                                    Biz unit description
                                </b>
                            </label>
                        </section>
                        <section>
                            <label className="checkbox">
                                <input
                                    type="checkbox"
                                    className="checkbox style-0"
                                    checked={!!bizInfo.cache_flag}
                                    onChange={this.onChangeCacheFlag.bind(this)}
                                />
                                <span>Use Cache</span>
                            </label>
                        </section>
                        {bizInfo.cache_flag ? (
                            <section>
                                <label className="input">
                                    Expire time(minute)
                                    <input
                                        type="number"
                                        value={bizInfo.cache_expire_time}
                                        onChange={this.onChangeCacheExpire.bind(
                                            this
                                        )}
                                    />
                                </label>
                            </section>
                        ) : (
                            ''
                        )}

                        {typeof bizInfo.end_point !== 'undefined' && (
                            <>
                                <section>
                                    <label className="label">
                                        <span className="text-danger">*</span>{' '}
                                        Documents
                                    </label>
                                    <label className="input">
                                        <a
                                            href={documentUrl}
                                            rel="noopener noreferrer"
                                            target="_blank">
                                            {documentUrl}
                                        </a>
                                    </label>
                                </section>

                                <section>
                                    <label className="label">
                                        <span className="text-danger">*</span>{' '}
                                        UID
                                    </label>
                                    <label className="input">
                                        <span className="ellipsis">
                                            {bizInfo.biz_uid}
                                        </span>{' '}
                                        <a
                                            className="copy-btn"
                                            data-clipboard-text={
                                                bizInfo.biz_uid
                                            }
                                            onClick={e => e.preventDefault()}>
                                            <i className="fa fa-copy" />
                                        </a>
                                    </label>
                                </section>
                                {editable ? (
                                    <section className="text-right">
                                        <button
                                            type="submit"
                                            style={{ padding: '2px 10px' }}
                                            className="btn btn-primary">
                                            <i className="fa fa-plus" />{' '}
                                            {dictionary.button.save}
                                        </button>
                                    </section>
                                ) : (
                                    ''
                                )}
                            </>
                        )}
                    </fieldset>
                )}
            </form>
        );
    }
}
export default UnitProperty;
