import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
@inject('shareStore')
@observer
class Step6 extends Component {
    prev() {
        this.props.shareStore.prev();
    }
    reset() {
        this.props.shareStore.resetStore();
    }
    render() {
        const partnerId = this.props.shareStore.getPartnerId;
        const shareUrl = this.props.shareStore.getShareUrl;
        const expireDate = this.props.shareStore.getExpireDate;
        return (
            <>
                <h5>하단의 정보와 같이 설정되었습니다.</h5>
                <div className="list-group">
                    <div className="list-group-item">
                        <h4 className="list-group-item-heading">파트너 ID</h4>
                        <p className="list-group-item-text">{partnerId}</p>
                    </div>
                    <div className="list-group-item">
                        <h4 className="list-group-item-heading">URL 주소</h4>
                        <p className="list-group-item-text break-word">
                            {shareUrl}
                        </p>
                    </div>
                    <div className="list-group-item">
                        <h4 className="list-group-item-heading">
                            로그인 만료일
                        </h4>
                        <p className="list-group-item-text">{expireDate}</p>
                    </div>
                </div>
                <p className="text-right">
                    <button
                        type="button"
                        onClick={this.prev.bind(this)}
                        className="btn btn-primary prev-step">
                        <i className="fa fa-arrow-left" /> 이전
                    </button>{' '}
                    <button
                        type="button"
                        onClick={this.reset.bind(this)}
                        className="btn btn-primary next-step">
                        <i className="fa fa-repeat" /> 재설정
                    </button>
                </p>
            </>
        );
    }
}

export default Step6;
