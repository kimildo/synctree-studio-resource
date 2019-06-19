import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Redirect, withRouter } from 'react-router-dom';
import { Helmet } from 'react-helmet';
import LayoutAuth from '../../layout/LayoutAuth';
import Request from '../../library/utils/Request';
@withRouter
@inject('userInfoStore')
@observer
class Auth extends Component {
    formEl = React.createRef();
    request = Request;
    state = {
        email: '',
        password: '',
        remember: 0,
        csrf_name: null,
        csrf_value: null,
    };

    componentDidMount() {
        if (
            !this.props.userInfoStore.isLoggedIn &&
            (this.state.csrf_name === null || this.state.csrf_value === null)
        ) {
            this.request
                .get('/auth/signin')
                .then(res => res.data.data)
                .then(data => {
                    this.setState({
                        csrf_name: data.csrf.csrf_name,
                        csrf_value: data.csrf.csrf_value,
                        email: data.remember || '',
                        remember: data.remember ? 1 : 0,
                    });
                });
        }
    }
    onChangeEmail(e) {
        this.setState({
            email: e.target.value,
        });
    }
    onChangePassword(e) {
        this.setState({
            password: e.target.value,
        });
    }
    onChangeRemember(e) {
        this.setState({
            remember: e.target.checked,
        });
    }
    signin(e) {
        e.preventDefault();
        this.request
            .post('/auth/signinCallback', this.state)
            .then(res => res.data.data)
            .then(data => {
                this.props.userInfoStore.setUserInfo(data.user_data);
                return <Redirect to="/" />;
            });
    }

    render() {
        if (this.props.userInfoStore.isLoggedIn) {
            return <Redirect to="/" />;
        }

        return (
            <LayoutAuth>
                <Helmet>
                    <title>Sign in - Synctree Studio V2.0 </title>
                </Helmet>
                <div className="aside">
                    <div className="logo">
                        <div className="logo_img">
                            <img
                                src="/htdocs/img/logo@3x.png"
                                aria-hidden
                                alt="Synctree Studio logo Image"
                            />
                        </div>
                    </div>
                    <div className="sign_in">
                        <form
                            method="POST"
                            ref={this.formEl}
                            onSubmit={this.signin.bind(this)}
                            noValidate="novalidate">
                            <p className="sign_in_text">Sign in</p>
                            <div className="sign_input_area">
                                <div className="sign_input">
                                    <p>E-mail</p>
                                    <input
                                        type="email"
                                        required
                                        onChange={this.onChangeEmail.bind(this)}
                                        value={this.state.email}
                                    />
                                </div>
                                <div className="sign_input">
                                    <p>Password</p>
                                    <input
                                        type="password"
                                        required
                                        onChange={this.onChangePassword.bind(
                                            this
                                        )}
                                        value={this.state.password}
                                    />
                                </div>
                            </div>

                            <div className="sign_box">
                                <input
                                    type="checkbox"
                                    onChange={this.onChangeRemember.bind(this)}
                                    checked={
                                        this.state.remember === 1 ? true : false
                                    }
                                />
                                <span className="alignleft">Remember Me</span>
                                <a id="forgot-pass" href="#none">
                                    <span className="alignright">
                                        Forgot ID/PW
                                    </span>
                                </a>
                            </div>
                            <button type="submit">
                                <span className="sign_in_btn">Sign in</span>
                            </button>
                        </form>
                    </div>
                </div>
            </LayoutAuth>
        );
    }
}

export default Auth;
