import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { withRouter, Link } from 'react-router-dom';
import _ from 'lodash';
import Header from './Header';
import Footer from './Footer';
import Loading from '../../Loading';

/* global global_data */

@inject('bizStore')
@observer
class Wrapper extends Component {
    componentDidMount() {
        const { app_id, bunit_id } = this.props.match.params;
        this.props.bizStore.getBizUnit(app_id, bunit_id);
    }
    getTabCls(path) {
        return _.includes(this.props.location.pathname, path) ? 'active' : '';
    }
    render() {
        // const isset = this.props.bizStore.isSetData;
        const { match, bizStore } = this.props,
            isset = bizStore.isSetData;

        return (
            <>
                {isset ? (
                    <>
                        <Header />
                        <div className="inbox-body no-content-padding">
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
                                                <li
                                                    className={this.getTabCls
                                                        .bind(this, '/unitprop')
                                                        .call()}>
                                                    <Link
                                                        to={`${
                                                            match.url
                                                        }/unitprop`}>
                                                        <i className="fa fa-fw fa-lg fa-gear" />
                                                        Unit Property
                                                    </Link>
                                                </li>
                                            ) : (
                                                ''
                                            )}

                                            <li
                                                className={this.getTabCls
                                                    .bind(this, '/unitflow')
                                                    .call()}>
                                                <Link
                                                    to={`${
                                                        match.url
                                                    }/unitflow`}>
                                                    <i className="fa fa-fw fa-lg fa-sitemap" />
                                                    Unit Flow
                                                </Link>
                                            </li>
                                        </ul>

                                        <div className="tab-content padding-15">
                                            {this.props.children}
                                        </div>
                                    </article>
                                </div>
                            </div>
                        </div>
                        <Footer />
                    </>
                ) : (
                    <Loading />
                )}
            </>
        );
    }
}
export default withRouter(Wrapper);
