import React, { Component } from 'react';
import { withRouter } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
import ReactLoading from 'react-loading';

import Util from '../../library/utils/Util';
import Request from '../../library/utils/Request';
/* global global_data */
import DeployHistory from './DeployHistory';

@withRouter
@inject('bizOpsStore', 'modalStore', 'userInfoStore')
@observer
class BizContent extends Component {
    exportType = React.createRef();
    request = Request;
    state = {
        getHistory: false,
        historyData: [],
        historyLoading: false,
    };
    deleteBizOps() {
        const { dictionary } = global_data;
        const { app_id, biz_id } = this.props.biz;

        Util.confirmMessage(
            `${dictionary.alert.warn}!`,
            dictionary.alert.ask
        ).then(() => {
            this.props.bizOpsStore.deleteBizOps({
                app_id: app_id,
                biz_id: biz_id,
            });
        });
    }
    modifyBizOps() {
        const { app_id, biz_id } = this.props.biz;
        this.props.history.push(
            `/console/apps/bunit/${app_id}/modify/${biz_id}/unitprop`
        );
    }
    testBizOps() {
        const {
            app_id,
            biz_id,
            operators,
            request_method_code,
        } = this.props.biz;
        if (operators.length === 0 || !request_method_code) {
            Util.showSmallBox(
                'error_message',
                1000,
                '세팅 후 테스트가 가능합니다.'
            );
            return false;
        }

        this.props.modalStore.showModal({
            type: 'TestBiz',
            data: {
                app_id: app_id,
                biz_id: biz_id,
            },
        });
    }
    sharePartner() {
        const { biz } = this.props;
        this.props.modalStore.showModal({
            type: 'BunitShare',
            data: {
                biz: biz,
            },
        });
    }
    export() {
        const { app_id, biz_id } = this.props.biz;
        const { dictionary } = global_data;
        Util.confirmMessage(dictionary.alert.noti, dictionary.alert.ask).then(
            () => {
                Util.showSmallBox();
                this.request
                    .post('/console/deploy', {
                        app_id: parseInt(app_id),
                        biz_id: biz_id,
                    })
                    .then(res => {
                        if (!!res) {
                            Util.showSmallBox('success_landing');
                            this.props.bizOpsStore.loadBizOps();
                        }
                    });
            }
        );
    }
    exportBizOps() {
        // const stat = this.exportType.current.value;
        // switch (stat) {
        //     case '4':
        //         this.export();
        //         break;
        //     default:
        //         this.sharePartner();
        //         break;
        // }
        this.export();
    }
    getDeployList = () => {
        const { app_id, biz_id } = this.props.biz,
            { historyLoading } = this.state;

        if (!!historyLoading) {
            return false;
        }

        this.setState({
            historyLoading: true,
        });
        this.request
            .post('/console/deploy/getDeployList', {
                app_id: parseInt(app_id),
                biz_id: biz_id,
            })
            .then(res => res.data.data)
            .then(data => {
                this.setState({
                    getHistory: true,
                    historyData: data,
                    historyLoading: false,
                });
            });
    };
    render() {
        const { biz } = this.props,
            { historyLoading } = this.state;

        const sw = biz.has_build_history ? 110 : 195;

        // console.log('bizContent', biz.has_build_history, biz.last_build);

        return (
            <div className="biz-list">
                <div className="row">
                    <div className="col-lg-8 col-sm-12">
                        <h2>{biz.biz_name}</h2>
                        <p className="desc">{biz.biz_desc}</p>
                        <p className="info">
                            <span>{biz.operators.length} Operators</span>
                            <span>{biz.operators.length} partners</span>
                            {biz.last_build !== null ? (
                                <span>
                                    Last Build Version :{' '}
                                    {biz.last_build.biz_ops_version} (
                                    {biz.last_build.build_date}){' '}
                                </span>
                            ) : (
                                ''
                            )}
                        </p>
                    </div>
                    <div className="col-lg-4 col-sm-12">
                        <p className="btn-wrap">
                            <button
                                type="button"
                                onClick={this.modifyBizOps.bind(this)}
                                className="modify">
                                Modify
                            </button>
                            <button
                                type="button"
                                onClick={this.testBizOps.bind(this)}
                                className="test">
                                Test
                            </button>
                            <button
                                type="button"
                                onClick={this.deleteBizOps.bind(this)}
                                className="delete">
                                Delete
                            </button>
                        </p>
                        <div>
                            <p>Unit Status : </p>
                            <div>
                                <select
                                    className="form-control status"
                                    ref={this.exportType}
                                    style={{ width: sw }}>
                                    <option value="1">Mock up</option>
                                    <option value="2">Dev</option>
                                    <option value="3">UAT</option>
                                    <option value="4">PUBLISHED</option>
                                </select>{' '}
                                <button
                                    type="button"
                                    // className="export"
                                    className="btn btn-primary"
                                    onClick={this.exportBizOps.bind(this)}
                                    style={{ width: '90px' }}>
                                    Export
                                </button>{' '}
                                {biz.has_build_history ? (
                                    <button
                                        type="button"
                                        onClick={this.getDeployList}
                                        // className="button-history"
                                        className={`btn btn-warning ${
                                            historyLoading ? 'disabled' : ''
                                        }`}
                                        style={{ width: '80px' }}>
                                        {historyLoading ? (
                                            <i className="fa fa-circle-o-notch fa-spin" />
                                        ) : (
                                            'history'
                                        )}
                                    </button>
                                ) : (
                                    ''
                                )}
                            </div>
                        </div>
                    </div>
                </div>
                {!!this.state.getHistory ? (
                    <div className="history-data">
                        {this.state.historyData > 0 ? (
                            <DeployHistory
                                historyData={this.state.historyData}
                                biz={biz}
                            />
                        ) : (
                            <div className="alert alert-warning">
                                서비스 준비중입니다.
                            </div>
                        )}
                    </div>
                ) : (
                    ''
                )}
            </div>
        );
    }
}
export default BizContent;
