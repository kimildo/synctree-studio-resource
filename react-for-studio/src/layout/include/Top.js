import React, { Component } from 'react';
import { Link, withRouter } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
import Util from '../../library/utils/Util';
/* global global_data  */
@inject('userInfoStore', 'appsStore')
@observer
class Top extends Component {
    constructor(props) {
        super(props);
        this.userInfo = this.props.userInfoStore.getUserInfo;
    }
    signOut() {
        Util.confirmMessage(
            'Logout ?',
            'You can improve your security further after logging out by closing this opened browser.'
        ).then(() => {
            this.props.userInfoStore.signOut();
        });
    }

    changeSelectApp(app) {
        this.props.userInfoStore.setSelectedApp(app);
    }

    getAppsLayer(appId) {
        let apps = this.props.appsStore.getApps;
        let _this = this;
        //  key = {`AppContent_${app.app.id}`
        //}
        if (!!apps) {
            return apps.map(app => {
                return (
                    <li
                        key={`top_apps_${app.app_id}`}
                        className={app.app_id === appId ? 'active' : ''}>
                        <button onClick={_this.changeSelectApp.bind(this, app)}>
                            {app.app_name}
                        </button>
                    </li>
                );
            });
        }
        return '';
    }
    componentDidMount() {
        if (!this.props.appsStore.getApps) {
            this.props.appsStore.loadApps();
        }
    }

    render() {
        const { dictionary } = global_data;
        let selectedApp = this.props.userInfoStore.getSelectedApp;
        let appsLayer = selectedApp
            ? this.getAppsLayer(selectedApp.app_id)
            : '';

        return (
            <header id="header">
                <div id="logo-group">
                    <span id="logo">
                        <Link to="/">
                            <img
                                src="/htdocs/img/logo-s-synctree@3x.png"
                                alt="Synctree Studio"
                            />
                        </Link>
                    </span>
                </div>

                <div className="project-context user-info">
                    <span
                        className="project-selector dropdown-toggle"
                        data-toggle="dropdown">
                        <span id="current_product">
                            {selectedApp
                                ? selectedApp.app_name
                                : 'Not found apps'}
                        </span>{' '}
                        <i className="fa fa-angle-down" />
                    </span>
                    <ul className="dropdown-menu">
                        {appsLayer}
                        {/* <li className="divider" />
                        <li>
                            <button className="button-add-app">
                                <i className="fa fa-plus-circle" />{' '}
                                {dictionary.button.add_app}
                            </button>
                        </li> */}
                    </ul>
                </div>
                <div className="pull-right">
                    <ul className="header-dropdown-list hidden-xs">
                        <span
                            className="dropdown-toggle"
                            data-toggle="dropdown">
                            <img
                                src={`/htdocs/img/blank.gif`}
                                className="flag flag-us"
                                alt={dictionary.button[dictionary['lang']]}
                            />
                        </span>
                        <ul className="dropdown-menu pull-right">
                            <li>
                                <button className="choose-lang ko">
                                    <img
                                        src={`/htdocs/img/blank.gif`}
                                        className="flag flag-kr"
                                        alt={dictionary.button.ko}
                                    />{' '}
                                    {dictionary.button.ko}
                                </button>
                            </li>
                            <li className="active">
                                <button className="choose-lang en">
                                    <img
                                        src={`/htdocs/img/blank.gif`}
                                        className="flag flag-us"
                                        alt={dictionary.button.en}
                                    />{' '}
                                    {dictionary.button.en}
                                </button>
                            </li>
                        </ul>
                    </ul>
                    <div className="project-context hidden-xs">
                        <span
                            className="project-selector dropdown-toggle"
                            data-toggle="dropdown"
                            aria-expanded="false">
                            <img
                                src={`/htdocs/img/avatars/male.png`}
                                alt="me"
                                className="online"
                            />{' '}
                            {this.userInfo.email}{' '}
                            <i className="fa fa-angle-down" />
                        </span>

                        <ul className="dropdown-menu user-menu">
                            {/* <li>
                                <p>2018.11.21</p>
                            </li>
                            <li>
                                <p>BizUnit</p>
                            </li>
                            <li>
                                <p>Commax</p>
                            </li>
                            <li className="divider" /> */}

                            <li>
                                <button onClick={this.signOut.bind(this)}>
                                    Log out
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>
        );
    }
}

export default withRouter(Top);
