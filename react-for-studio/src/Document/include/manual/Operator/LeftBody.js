import React, { Component } from 'react';
import _ from 'lodash';

import Parameters from '../../params/Parameters';

class LeftBody extends Component {
    getHeaderType(code) {
        switch (code) {
            case 2:
            default:
                return 'application/json';
            case 3:
                return 'application/x-www-form-urlencoded';
            case 4:
                return 'application/xml';
        }
    }
    getSampleCode(url) {
        this.props.getSampleCode(url);
    }

    render() {
        const { op, loading, lang } = this.props;
        const authKeyArrs = op.auth_keys_array || null;

        return (
            <>
                <h3 className="FD_head_txt" id={op.op_id}>
                    {op.req_method === 'P' ? (
                        <span className="FD_H_color_post">POST</span>
                    ) : (
                        <span className="FD_H_color_get">GET</span>
                    )}
                    {op.op_name}
                </h3>

                <div className="FD_H_url">{op.target_url}</div>
                {op.protocol_type_code === 1 ? (
                    <button
                        className="btn-sample-code"
                        onClick={this.getSampleCode.bind(this, op.target_url)}>
                        {!!loading ? (
                            <i className="fa fa-circle-o-notch fa-spin" />
                        ) : (
                            `Get Sample Code(${lang})`
                        )}
                    </button>
                ) : (
                    ''
                )}

                <h3>DESCRIPTION</h3>
                <p className="FD_des_txt">{op.op_desc || '...'}</p>
                {op.target_method ? (
                    <>
                        <h3>URL</h3>
                        <p className="FD_des_txt">/auth/signin</p>
                    </>
                ) : (
                    ''
                )}
                {op.header_transfer_type_code > 1 ? (
                    <div className="headerInfo">
                        <p className="HI_title">HEADERS</p>
                        <div className="HI_dubleP HI_normalP">
                            <p className="HI_subtitle">Content-Type</p>
                            <p className="HI_subdetail">
                                <span className="ST_E">
                                    {this.getHeaderType(
                                        op.header_transfer_type_code
                                    )}
                                </span>
                            </p>
                        </div>
                    </div>
                ) : (
                    ''
                )}

                {op.auth_type_code > 0 ? (
                    <div className="headerInfo">
                        <p className="HI_title">AUTHORIZATION</p>
                        {!!authKeyArrs ? (
                            <div className="HI_dubleP HI_normalP">
                                <p className="HI_subtitle">Type</p>
                                <p className="HI_subdetail">
                                    {op.auth_type_code === 1 ? (
                                        <>
                                            <span className="ST_E">Basic</span>{' '}
                                            <br />
                                            <span className="ST_K">
                                                {authKeyArrs[0].username} /{' '}
                                                {authKeyArrs[0].password}
                                            </span>
                                        </>
                                    ) : (
                                        <>
                                            <span className="ST_E">
                                                Bearer Token
                                            </span>{' '}
                                            <br />
                                            <span className="ST_K">
                                                {authKeyArrs[0].token}
                                            </span>
                                        </>
                                    )}
                                    <span className="ST_E">
                                        {this.getHeaderType(
                                            op.header_transfer_type_code
                                        )}
                                    </span>
                                </p>
                            </div>
                        ) : (
                            ''
                        )}
                    </div>
                ) : (
                    ''
                )}
                {op.protocol_type_code > 0 ? (
                    <div className="headerInfo">
                        <p className="HI_title">PROTOCOL TYPE</p>
                        <div className="HI_dubleP HI_normalP">
                            <p className="HI_subtitle">Type</p>
                            <p className="HI_subdetail">
                                <span className="ST_E">
                                    {op.protocol_type_code === 1
                                        ? 'Secure Protocol'
                                        : 'URL Scheme'}
                                </span>
                            </p>
                        </div>
                    </div>
                ) : (
                    ''
                )}
                <div className="bodyInfo">
                    <p className="BI_title">Request Parameters</p>
                    <Parameters
                        params={op.request}
                        type={'req'}
                        id={`op_req_${op.op_id}`}
                    />
                </div>
                <div className="bodyInfo">
                    <p className="BI_title">Response</p>
                    <Parameters
                        params={op.response}
                        type={'res'}
                        id={`op_res_${op.op_id}`}
                    />
                </div>
            </>
        );
    }
}

export default LeftBody;
