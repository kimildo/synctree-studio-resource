import React, { Component } from 'react';
import { Switch, Route, withRouter, Link } from 'react-router-dom';
import { inject, observer } from 'mobx-react';

import MyLoadable from '../../MyLoadable';
import { Error404 } from '../../pages';

const UnitProperty = MyLoadable({
    loader: () => import(/* webpackChunkName: "u-u-b" */ './UnitProperty'),
});
const Timeline = MyLoadable({
    loader: () => import(/* webpackChunkName: "t-u-b" */ './Timeline'),
});

/* global global_data */
@withRouter
@inject('bizOpsStore', 'bizStore')
@observer
class Body extends Component {
    render() {
        const { app_id, bunit_id } = this.props.match.params;

        return (
            <div id="inbox-content" className="inbox-body no-content-padding">
                <div
                    className="table-wrap custom-scroll animated fast fadeInRight"
                    style={{
                        opacity: 1,
                        marginLeft: 0,
                    }}>
                    <div>
                        <article
                            style={{
                                minWidth: '984px',
                            }}>
                            <ul className="nav nav-tabs bordered">
                                {!global_data.partner ? (
                                    <li className="active">
                                        <Link
                                            to={`/console/apps/bunit/${app_id}/modify/${bunit_id}/unitprop`}>
                                            <i className="fa fa-fw fa-lg fa-gear" />
                                            Unit Property
                                        </Link>
                                    </li>
                                ) : (
                                    ''
                                )}

                                <li
                                    className={
                                        global_data.partner ? 'active' : ''
                                    }>
                                    <Link
                                        to={`/console/apps/bunit/${app_id}/modify/${bunit_id}/unitflow`}>
                                        <i className="fa fa-fw fa-lg fa-sitemap" />
                                        Unit Flow
                                    </Link>
                                </li>
                            </ul>

                            <div className="tab-content padding-15">
                                <Switch>
                                    <Route
                                        exact
                                        path="/console/apps/bunit/:app_id/modify/:bunit_id"
                                        component={UnitProperty}
                                    />
                                    <Route
                                        exact
                                        path="/console/apps/bunit/:app_id/modify/:bunit_id/unitflow"
                                        component={Timeline}
                                    />
                                    <Route component={Error404} />
                                </Switch>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        );
    }
}
export default Body;
