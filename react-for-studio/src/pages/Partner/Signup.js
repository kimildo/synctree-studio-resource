import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Redirect, withRouter } from 'react-router-dom';
import Loading from '../../Loading';
import LayoutAuth from '../../layout/LayoutAuth';
import SignupContent from './SignupContent';
import Request from '../../library/utils/Request';
@withRouter
@inject('partnerStore')
@observer
class Signup extends Component {
    constructor(props) {
        super(props);
        this.formEl = React.createRef();
        this.state = {
            csrf_name: null,
            csrf_value: null,
            access_key: '',
            allow: 0,
            password: '',
            rePassword: '',
        };
        this.request = Request;
    }
    componentDidMount() {
        const { code } = this.props.match.params;
        if (!code) {
            this.setState({ allow: 3 });
            return false;
        }
        this.props.partnerStore.setCode(code);
        if (this.state.csrf_name === null || this.state.csrf_value === null) {
            this.request
                .get(`/partner/signup/${code}`)
                .then(res => res.data.data)
                .then(data => {
                    this.props.partnerStore.setBaseInfo({
                        partner_id: data.partner.partner_id,
                        biz_name: data.partner.biz_name,
                        operator: data.partner.operator,
                        client_name: data.partner.client_name,
                    });

                    this.setState({
                        access_key: data.partner.access_key,
                        csrf_name: data.csrf.csrf_name,
                        csrf_value: data.csrf.csrf_value,
                        allow: data.partner.account_status_code,
                    });
                });
        }
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
    signUp({ password, rePassword }) {
        let params = {
            password: password,
            'password-re': rePassword,
            csrf_name: password,
            csrf_value: password,
            access_key: password,
        };

        this.request.post('/partner/passwordSetCallback', params).then(res => {
            // let { data } = res.data;
            // this.props.userInfoStore.setUserInfo(data.user_data);
            this.setState({
                allow: 2,
            });
        });
    }

    renderContents() {
        const { allow } = this.state;
        // this.state.allow

        switch (allow) {
            case 0:
            default:
                // 0 : loading
                return <Loading />;
            case 1: // 1 : 현재 페이지 그대로
                return <SignupContent signUp={this.signUp.bind(this)} />;
            case 2: // 2 : 로그인 화면 이동
                return <Redirect to="/partner/signin" />;
            case 3: // 3 : 비활성화(접근불가)
                return <div>Not Allowed!!!</div>;
        }
    }

    render() {
        const contents = this.renderContents();
        return <LayoutAuth>{contents}</LayoutAuth>;
    }
}

export default Signup;
