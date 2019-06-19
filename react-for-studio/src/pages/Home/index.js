import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Helmet } from 'react-helmet';
import NoLeft from '../../layout/NoLeft';
import { Link, withRouter } from 'react-router-dom';
/* global global_data */
@withRouter
@inject('appsStore')
@observer
class Home extends Component {
    render() {
        const { dictionary } = global_data;
        return (
            <NoLeft noHeader={false} noFooter={false}>
                <Helmet>
                    <title>Synctree Studio V2.0 </title>
                </Helmet>
                <div role="main">
                    <div id="wrapper">
                        <div id="notice">
                            [공지] 공지사항 텍스트가 들어갑니다.
                        </div>

                        <div className="home-content">
                            <ul>
                                <li>
                                    <Link to="/console/dashboard">
                                        <p className="img dash" />

                                        <p className="txt">
                                            {dictionary.aside.dashboard}
                                        </p>
                                    </Link>
                                </li>
                                <li>
                                    <Link to="/console/apps">
                                        <p className="img apps" />

                                        <p className="txt">
                                            {dictionary.aside.apps}
                                        </p>
                                    </Link>
                                </li>
                                <li>
                                    <Link to="/console/marketing">
                                        <p className="img marketing" />

                                        <p className="txt">
                                            {dictionary.aside.marketing}
                                        </p>
                                    </Link>
                                </li>
                                <li>
                                    <Link to="/console/management">
                                        <p className="img management" />

                                        <p className="txt">
                                            {dictionary.aside.management}
                                        </p>
                                    </Link>
                                </li>
                                <li>
                                    <Link to="/console/b2b">
                                        <p className="img b2b" />

                                        <p className="txt">
                                            {dictionary.aside.B2B}
                                        </p>
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </NoLeft>
        );
    }
}

export default Home;
