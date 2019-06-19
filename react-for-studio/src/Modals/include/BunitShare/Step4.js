import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import StepBottom from './StepBottom';
@inject('shareStore')
@observer
class Step4 extends Component {
    constructor(props) {
        super(props);
        this.f = React.createRef();
    }
    onChangeExpire(e) {
        this.props.shareStore.setExpire(e.target.value);
    }

    render() {
        const expire = this.props.shareStore.getExpireDate;
        return (
            <>
                <h5>접속 허가 기간을 설정 해주세요.</h5>
                <form ref={this.f} className="input-group">
                    <input
                        type="date"
                        name="partner-connect-expire"
                        className="form-control"
                        placeholder="접속 허가기간"
                        onChange={this.onChangeExpire.bind(this)}
                        value={expire}
                        required
                    />
                </form>
                <StepBottom form={this.f} />
            </>
        );
    }
}

export default Step4;
