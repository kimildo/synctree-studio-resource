import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Redirect, withRouter } from 'react-router-dom';
import NoLeft from '../../layout/NoLeft';
import Request from '../../library/utils/Request';
@withRouter
@inject('partnerStore')
@observer
class Unit extends Component {
    constructor(props) {
        super(props);

        this.request = Request;
    }
    componentDidMount() {}

    render() {
        if (!this.props.partnerStore.isLoggedIn) {
            return <Redirect to="/partner/signin" />;
        }

        return <NoLeft>asd</NoLeft>;
    }
}

export default Unit;
