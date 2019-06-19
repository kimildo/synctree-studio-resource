import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import ReactLoading from 'react-loading';
import axios from 'axios';

import LeftBody from './LeftBody';
import RightBody from './RightBody';

@inject('docsStore')
@observer
class OperatorEach extends Component {
    request = axios.create();
    state = { code: null, type: 'php', loading: false };
    getSampleCode() {
        const { docsStore, d } = this.props,
            { language, langList } = docsStore;
        if (this.state.loading) {
            return false;
        }

        this.setState({ loading: true });

        const endpointUrl = `${docsStore.getData.end_point}/getSampleCodes`;
        this.request
            .post(endpointUrl, {
                app_id: docsStore.getData.app_id,
                biz_id: docsStore.getData.biz_id,
                target_url: d.target_url,
                snipet: langList[language], // select language 에 따라 변화
            })
            .then(res => res.data.data)
            .then(data =>
                this.setState({
                    code: data.code,
                    type: data.type,
                    loading: false,
                })
            );
    }
    componentDidUpdate(prevProps, prevState) {
        const { code, loading } = prevState;
        if (!!code && !loading) {
            this.getSampleCode();
        }
    }
    render() {
        const { d, docsStore } = this.props,
            { loading, code } = this.state;
        return (
            <section className="docBody_FD">
                <div className="leftBody_FD">
                    <div className="mainTxt_FD">
                        {/* <div className="FD_H_url">{d.target_url}</div>
                        {d.protocol_type_code === 1 ? (
                            <button
                                className="btn-sample-code"
                                onClick={this.getSampleCode.bind(
                                    this,
                                    d.target_url
                                )}>
                                {!!loading ? (
                                    <i className="fa fa-circle-o-notch fa-spin" />
                                ) : (
                                    `Get Sample Code(${docsStore.language})`
                                )}
                            </button>
                        ) : (
                            ''
                        )} */}
                        <LeftBody
                            op={d}
                            loading={loading}
                            lang={docsStore.language}
                            getSampleCode={this.getSampleCode.bind(this)}
                        />
                    </div>
                </div>
                {loading ? (
                    <div className="rightBody_FD">
                        <ReactLoading type={'bars'} width={'22%'} />
                    </div>
                ) : code !== null ? (
                    <RightBody {...this.state} />
                ) : (
                    ''
                )}
            </section>
        );
    }
}

export default OperatorEach;
