import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import BizContent from './BizContent';
import Loading from '../../Loading';

@inject('navStore', 'modalStore', 'userInfoStore', 'bizOpsStore')
@observer
class BizUnit extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Apps', 'Biz Unit']);
        this.updateSelectedApp();
        // if (!this.props.bizOpsStore.getBizOps) {
        this.props.bizOpsStore.loadBizOps(
            parseInt(this.props.match.params.app_id)
        );
        // }
    }
    componentDidUpdate() {
        if (!!this.props.userInfoStore.getSelectedApp) {
            if (
                parseInt(this.props.match.params.app_id) !==
                this.props.userInfoStore.getSelectedApp.app_id
            ) {
                this.updateSelectedApp();
            }
        }
    }

    updateSelectedApp() {
        this.props.userInfoStore.setSelectedAppByAppId(
            parseInt(this.props.match.params.app_id)
        );
    }

    addBizUnit() {
        this.props.modalStore.showModal({
            type: 'AddBiz',
            data: {
                appId: this.props.match.params.app_id,
            },
        });
    }
    goBack() {
        this.props.history.goBack();
    }
    getAppType(type) {
        switch (type) {
            case 1:
                return 'bizUnit';
            case 2:
                return 'Chatbot';
            case 3:
            default:
                return 'WebPage';
        }
    }

    getBizContents() {
        const bizOps = this.props.bizOpsStore.getBizOps;
        if (bizOps) {
            return bizOps.map(biz => {
                return (
                    <BizContent key={`BizContent_${biz.biz_id}`} biz={biz} />
                );
            });
        }
        return bizOps;
    }
    render() {
        const appInfo = this.props.userInfoStore.getSelectedApp;
        let bizLayer = this.getBizContents();

        const appType = this.getAppType(appInfo ? appInfo.app_type : null);
        return (
            <section>
                <Helmet>
                    <title>Biz Unit - Synctree Studio V2.0 </title>
                </Helmet>
                {!bizLayer ? (
                    <Loading />
                ) : (
                    <>
                        <div className="biz-top">
                            <button
                                className="back"
                                title="뒤로가기"
                                onClick={this.goBack.bind(this)}>
                                뒤로가기
                            </button>
                            <div className="biz-top-right">
                                <h1>{appInfo.app_name}</h1>
                                <p className="info">
                                    <span
                                        className={`app-type app-${
                                            appInfo.app_type
                                        }`}>
                                        {appType}
                                    </span>{' '}
                                    <span className="date">
                                        {appInfo.reg_date} 생성
                                    </span>{' '}
                                    <span className="status status-live">
                                        <em />
                                        LIVE
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div className="biz-content">
                            {bizLayer}
                            <button
                                onClick={this.addBizUnit.bind(this)}
                                className="biz-add">
                                Add Biz Unit
                            </button>
                        </div>
                    </>
                )}
            </section>
        );
    }
}

export default withRouter(BizUnit);
