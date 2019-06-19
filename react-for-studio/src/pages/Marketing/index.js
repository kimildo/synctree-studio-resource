import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
@withRouter
@inject('navStore')
@observer
class Marketing extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Marketing']);
    }
    render() {
        return (
            <div>
                <Helmet>
                    <title>Marketing - Synctree Studio V2.0 </title>
                </Helmet>
                <h2>Marketing 준비중</h2>;
            </div>
        );
    }
}

export default Marketing;
