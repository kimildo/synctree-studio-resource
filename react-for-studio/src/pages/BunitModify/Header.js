import React, { Component } from 'react';
import { withRouter, Link } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import Util from '../../library/utils/Util';
/* global global_data */
@withRouter
@inject('bizStore', 'modalStore')
@observer
class Header extends Component {
    constructor(props) {
        super(props);
        this.btnTest = React.createRef();
    }

    saveBizUnit() {
        const { dictionary } = global_data;
        const _this = this;
        Util.confirmMessage(
            dictionary.alert.warn + '!',
            dictionary.alert.modify_ask
        ).then(() => {
            _this.props.bizStore.saveBizUnit();
        });
    }

    buildBizUnit() {
        const { dictionary } = global_data;
        const _this = this;
        Util.confirmMessage(
            dictionary.alert.warn + '!',
            dictionary.alert.modify_ask
        ).then(() => {
            _this.props.bizStore.buildBizUnit();
        });
    }

    testBizUnit() {
        const { bizInfo } = this.props.bizStore;

        const opArray = _.flatMap(bizInfo.operators.map(row => row.data), n =>
            typeof n.length === 'number' ? _.flatten(n) : n
        );
        // request 세팅이 되었는지 체크
        if (bizInfo.request.length === 0) {
            this.props.modalStore.showModal({ type: 'EditRequest', data: '' });
            return;
        }
        if (opArray.length === 0) {
            Util.showSmallBox(
                'error_message',
                1000,
                'Operator 세팅 후 진행이 가능합니다.'
            );
            return;
        }

        this.props.modalStore.showModal({
            type: 'TestBiz',
            data: {
                app_id: bizInfo.app_id,
                biz_id: bizInfo.biz_id,
            },
        });
    }
    discardBizUnit() {
        const { dictionary } = global_data;
        const { app_id } = this.props.bizStore.bizInfo;

        Util.confirmMessage(
            dictionary.alert.warn + '!',
            dictionary.alert.delete_ask
        ).then(() => {
            this.props.bizStore.discardBizUnit().then(() => {
                this.props.history.push(`/console/apps/bunit/${app_id}`);
            });
        });
    }
    render() {
        const { bizInfo, activeBuild } = this.props.bizStore,
            { dictionary } = global_data;
        return (
            <>
                <div className="biz-top">
                    {!global_data.partner ? (
                        <Link
                            title="back"
                            className="back"
                            to={`/console/apps/bunit/${bizInfo.app_id}`}>
                            뒤로가기
                        </Link>
                    ) : (
                        ''
                    )}

                    <div className="biz-top-right">
                        <h1>
                            {bizInfo ? `${bizInfo.app_name} > ` : ''}
                            {bizInfo.biz_name}
                        </h1>

                        <div className="biz-btn-wrap">
                            {!global_data.partner ? (
                                <>
                                    <button
                                        className={`btn-save-bunit ${
                                            !!activeBuild ? 'active' : ''
                                        }`}
                                        onClick={this.buildBizUnit.bind(this)}>
                                        {'Build'}
                                    </button>{' '}
                                    {typeof bizInfo.end_point !==
                                    'undefined' ? (
                                        <button
                                            className="btn-test-bunit"
                                            ref={this.btnTest}
                                            onClick={this.testBizUnit.bind(
                                                this
                                            )}>
                                            {dictionary.button.test}
                                        </button>
                                    ) : (
                                        ''
                                    )}{' '}
                                    <span>
                                        <button
                                            className="bunit-del"
                                            onClick={this.discardBizUnit.bind(
                                                this
                                            )}>
                                            {dictionary.button.discard_unit}
                                        </button>
                                    </span>
                                </>
                            ) : (
                                ''
                            )}
                        </div>
                    </div>
                </div>
            </>
        );
    }
}
export default Header;
