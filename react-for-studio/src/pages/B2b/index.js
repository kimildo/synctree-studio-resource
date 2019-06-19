import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Link, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
@withRouter
@inject('navStore')
@observer
class B2b extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['B2B Gateway']);
    }
    render() {
        return (
            <div>
                <Helmet>
                    <title>B2B Gateway - Synctree Studio V2.0 </title>
                </Helmet>
                <h2>B2B Gateway 준비중</h2>
            </div>
        );
    }
}

export default B2b;
