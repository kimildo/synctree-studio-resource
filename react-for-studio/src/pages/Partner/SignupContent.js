import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Util from '../../library/utils/Util';
@inject('partnerStore')
@observer
class SignupContents extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.state = {
            password: '',
            rePassword: '',
        };
    }

    onChangePassword(e) {
        this.setState({
            password: e.target.value,
        });
    }
    onChangeRePassword(e) {
        this.setState({
            rePassword: e.target.checked,
        });
    }
    signUp(e) {
        e.preventDefault();
        if (Util.formCheckRequired(this.formEl)) {
            if (this.state.password === this.state.rePassword) {
                this.props.signUp(this.state);
            } else {
                Util.showSmallBox(
                    'error_message',
                    1000,
                    '비밀번호가 같지 않습니다.'
                );
            }
        }
    }

    render() {
        const baseInfo = this.props.partnerStore.getBaseInfo;
        const { password, rePassword } = this.state;

        return (
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
                        onSubmit={this.signUp.bind(this)}
                        noValidate="novalidate">
                        <p className="sign_in_text">Partners Sign up</p>

                        <div className="partner-info">
                            <p>
                                <span>Client</span> {baseInfo.client_name}
                            </p>
                            <p>
                                <span>Partner</span> {baseInfo.partner_id}
                            </p>
                            <p>
                                <span>App</span> {baseInfo.biz_name}
                            </p>
                            <p>
                                <span>Account</span> {baseInfo.partner_id}
                            </p>
                        </div>

                        <div className="sign_input partner_input">
                            <p>Password</p>
                            <input
                                type="password"
                                name="password"
                                value={password}
                                onChange={this.onChangePassword.bind(this)}
                                required
                            />
                        </div>
                        <div className="sign_input partner_input">
                            <p>Re-enter Password</p>
                            <input
                                type="password"
                                name="password-re"
                                value={rePassword}
                                onChange={this.onChangeRePassword.bind(this)}
                                required
                            />
                        </div>

                        <button type="submit">
                            <span className="sign_in_btn">Sign up</span>
                        </button>
                    </form>
                </div>
            </div>
        );
    }
}

export default SignupContents;
