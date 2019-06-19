import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
@withRouter
@inject('navStore')
@observer
class Management extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Management']);
    }
    render() {
        return (
            <div>
                <Helmet>
                    <title>Management - Synctree Studio V2.0 </title>
                </Helmet>
                <h2>Management 준비중</h2>;
            </div>
        );
    }
}

export default Management;
