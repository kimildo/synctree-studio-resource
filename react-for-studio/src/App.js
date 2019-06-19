import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Switch, Route, Redirect, withRouter } from 'react-router-dom';
import { Home, Auth, Console, Partner, Error404 } from './pages';
// for test
// import SvgTest from './test/SvgTest';
@withRouter
@inject('userInfoStore')
@observer
class App extends Component {
    onUnload(event) {
        // this.props.userInfoStore.signOut();
        // if (this.props.bizStore.doSave) {
        //     const confirmationMessage = 'Are you sure you want to leave?';
        //     event.returnValue = confirmationMessage;
        //     return confirmationMessage;
        // }
    }

    componentDidMount() {
        window.addEventListener('beforeunload', this.onUnload.bind(this));
        console.log(
            '%cWelcome to Synctree Studio',
            [
                'padding : 10px',
                'margin : 10px 0',
                'background : linear-gradient(#98004C, #FFE8F3)',
                'font-size : 25px',
                'font-weight : bold',
                'text-align : center',
                'color : #ffffff',
            ].join(';')
        );
    }

    // componentWillUnmount() {
    //     window.removeEventListener('beforeunload', this.onUnload.bind(this));
    // }

    checkLogin(C) {
        return !this.props.userInfoStore.isLoggedIn ? (
            <Redirect to="/signin" />
        ) : (
            <C />
        );
    }
    render() {
        if (
            !this.props.userInfoStore.isLoggedIn &&
            this.props.location.pathname !== '/signin'
        ) {
            return <Redirect to="/signin" />;
        }

        return (
            <Switch>
                <Route exact path="/signin" component={Auth} />
                <Route
                    exact
                    path="/"
                    render={this.checkLogin.bind(this, Home)}
                />
                <Route
                    path="/console"
                    render={this.checkLogin.bind(this, Console)}
                />
                <Route path="/partner" component={Partner} />
                {/* <Route exact path="/test" component={SvgTest} /> */}

                {/* The Default not found component */}
                <Route component={Error404} />
            </Switch>
        );
    }
}

export default App;
