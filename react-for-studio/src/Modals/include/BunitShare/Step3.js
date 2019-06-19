import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import StepBottom from './StepBottom';
import Request from '../../../library/utils/Request';
@inject('shareStore')
@observer
class Step3 extends Component {
    constructor(props) {
        super(props);
        this.f = React.createRef();
        this.state = {
            partners: [],
        };
    }

    componentDidMount() {
        Request.get('/console/apps/getPartnerList').then(res => {
            this.setState({ partners: res.data.data.partners });
        });
    }

    getPartnerLayer() {
        return this.state.partners.map(p => (
            <option key={`partners_${p.account_id}`} value={p.account_email}>
                {p.account_email}
            </option>
        ));
    }
    onChangePartnerName(e) {
        this.props.shareStore.setPartnerId(e.target.value);
    }

    render() {
        const partners = this.getPartnerLayer();
        const partnerId = this.props.shareStore.getPartnerId;
        return (
            <>
                <h5>파트너의 아이디를 정해주세요.</h5>
                <form ref={this.f} className="input-group">
                    <input
                        type="email"
                        className="form-control"
                        list="partner_list"
                        placeholder="파트너의 아이디"
                        onChange={this.onChangePartnerName.bind(this)}
                        value={partnerId}
                        required
                    />
                    <datalist id="partner_list">{partners}</datalist>
                </form>
                {/* <div className="alert alert-for-id alert-warning hide" role="alert">
                    파트너의 아이디를 입력하세요
                    <button
                        type="button"
                        className="close"
                        data-dismiss="alert"
                        aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div> */}
                <StepBottom form={this.f} />
            </>
        );
    }
}

export default Step3;
