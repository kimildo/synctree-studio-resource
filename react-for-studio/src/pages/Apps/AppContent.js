import React, { Component } from 'react';
import { Link, withRouter } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
import Util from '../../library/utils/Util';
/* global global_data */
@withRouter
@inject('userInfoStore', 'appsStore')
@observer
class AppContent extends Component {
    getAppTypeImg() {
        let { app } = this.props;
        switch (app.app_type) {
            case 1:
                return (
                    <img
                        src="/htdocs/img/apps/illust-biz@3x.png"
                        alt="bizOps"
                    />
                );
            case 2:
                return (
                    <img
                        src="/htdocs/img/apps/illust-chatbot@3x.png"
                        alt="Chatbot"
                    />
                );
            case 3:
            default:
                return (
                    <img
                        src="/htdocs/img/apps/illust-webpage@3x.png"
                        alt="WebPage"
                    />
                );
        }
    }
    deleteApp(app_id) {
        const { dictionary } = global_data;
        //let _this = this;

        Util.confirmMessage(
            `${dictionary.alert.warn}!`,
            dictionary.alert.ask
        ).then(() => {
            this.props.appsStore.deleteApp(app_id);
        });
    }
    render() {
        const { dictionary } = global_data;
        let { app } = this.props;
        let appBg = this.getAppTypeImg();
        let { name } = this.props.userInfoStore.getUserInfo;

        return (
            <>
                <div className="swiper-slide">
                    <div className="swiper-content">
                        <div className="design">
                            <p>{appBg}</p>
                            <h5>{app.app_name}</h5>
                            {app.is_new ? (
                                <div className="new">
                                    <i className="fa fa-star" />
                                </div>
                            ) : (
                                ''
                            )}
                        </div>
                        <div className="bottom">
                            <span className="user-id">{name}</span>{' '}
                            <span className="date">{app.reg_date}</span>{' '}
                            <Link
                                className="enter app-enter"
                                to={`/console/apps/bunit/${app.app_id}`}>
                                <i className="fa fa-arrow-right" />
                            </Link>
                            <a
                                className="button-app-delete"
                                onClick={this.deleteApp.bind(this, app.app_id)}
                                rel="tooltip"
                                title=""
                                data-placement="right"
                                data-original-title={dictionary.button.remove}
                                href="javascript:void(0);"
                            />
                        </div>
                    </div>
                </div>
            </>
        );
    }
}
export default AppContent;
