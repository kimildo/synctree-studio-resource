import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Switch, Route, Redirect, withRouter } from 'react-router-dom';

import MyLoadable from '../../MyLoadable';
import { Error404 } from '../../pages';

const Signup = MyLoadable({
    loader: () => import(/* webpackChunkName: "su-p-i" */ './Signup'),
});

const Signin = MyLoadable({
    loader: () => import(/* webpackChunkName: "si-p-i" */ './Signin'),
});

const Unit = MyLoadable({
    loader: () => import(/* webpackChunkName: "u-p-i" */ './Unit'),
});

@inject('partnerStore')
@observer
class Partner extends Component {
    checkLogin() {
        const { match } = this.props;
        return !this.props.partnerStore.isLoggedIn ? (
            <Redirect to={`${match.path}/signin`} />
        ) : (
            <Unit />
        );
    }
    render() {
        const { match } = this.props;
        return (
            <Switch>
                <Route
                    exact
                    path={`${match.path}/signup/:code`}
                    component={Signup}
                />
                <Route exact path={`${match.path}/signin`} component={Signin} />
                <Route
                    exact
                    path={`${match.path}/unit`}
                    render={this.checkLogin.bind(this)}
                />
                <Route component={Error404} />
            </Switch>
        );
    }
}

export default withRouter(Partner);
