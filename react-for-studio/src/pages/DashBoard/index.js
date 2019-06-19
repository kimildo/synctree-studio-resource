import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';

@inject('navStore')
@observer
class Dashboard extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Dashboard']);
    }
    render() {
        return (
            <div>
                <Helmet>
                    <title>Dashboard - Synctree Studio V2.0 </title>
                </Helmet>
                <h2>Dashboard 준비중</h2>;
            </div>
        );
    }
}

export default withRouter(Dashboard);
