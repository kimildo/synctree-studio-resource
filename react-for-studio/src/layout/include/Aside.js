import React, { Component } from 'react';
import { Link, withRouter } from 'react-router-dom';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import AsideApps from './AsideApps';
/* global global_data  */

@inject('userInfoStore', 'appsStore')
@observer
class Aside extends Component {
    getClsName(path) {
        return _.includes(this.props.location.pathname, `/console${path}`)
            ? 'active'
            : '';
    }
    getAppClsName(app_id) {
        return _.includes(
            this.props.location.pathname,
            `/console/apps/bunit/${app_id}`
        )
            ? 'active'
            : '';
    }

    render() {
        const { dictionary } = global_data;
        const teamId = !!this.props.userInfoStore.getUserInfo
            ? this.props.userInfoStore.getUserInfo.team_id
            : null;

        // console.log('teamId', teamId);

        return (
            <aside id="left-panel">
                <nav>
                    <ul>
                        <li
                            className={this.getClsName
                                .bind(this, '/dashboard')
                                .call()}>
                            <Link to="/console/dashboard">
                                <span className="icon icon-dash-board" />
                                <span className="menu-item-parent">
                                    Dashboard
                                </span>
                            </Link>
                        </li>
                        <li
                            className={this.getClsName
                                .bind(this, '/apps')
                                .call()}>
                            <Link to="/console/apps">
                                <span className="icon icon-apps" />
                                <span className="menu-item-parent">
                                    {dictionary.aside.apps}{' '}
                                </span>
                            </Link>
                            <AsideApps path={this.props.location.pathname} />
                        </li>
                        <li
                            className={this.getClsName
                                .bind(this, '/marketing')
                                .call()}>
                            <Link to="/console/marketing">
                                <span className="icon icon-marketing" />
                                <span className="menu-item-parent">
                                    {dictionary.aside.marketing}
                                </span>
                            </Link>
                        </li>
                        <li
                            className={this.getClsName
                                .bind(this, '/management')
                                .call()}>
                            <Link to="/console/management">
                                <span className="icon icon-management" />
                                <span className="menu-item-parent">
                                    {dictionary.aside.management}
                                </span>
                            </Link>
                        </li>
                        <li
                            className={this.getClsName
                                .bind(this, '/b2b')
                                .call()}>
                            <Link to="/console/b2b">
                                <span className="icon icon-b2b" />
                                <span className="menu-item-parent">
                                    {dictionary.aside.B2B}
                                </span>
                            </Link>
                        </li>
                        <li
                            className={this.getClsName
                                .bind(this, '/op')
                                .call()}>
                            <Link to="/console/op/list">
                                <span className="icon icon-b2b" />
                                <span className="menu-item-parent">
                                    {dictionary.aside.op}
                                </span>
                            </Link>
                        </li>
                        {teamId !== null ? (
                            <li>
                                <a
                                    onClick={() => alert('not ready')}
                                    className="not-ready">
                                    <span className="icon icon-b2b" />
                                    <span className="menu-item-parent">
                                        Team List
                                    </span>
                                </a>
                            </li>
                        ) : (
                            ''
                        )}
                    </ul>
                </nav>
                <span className="minifyme" data-action="minifyMenu">
                    <i className="fa fa-arrow-circle-left hit" />
                </span>
            </aside>
        );
    }
}
export default withRouter(Aside);
