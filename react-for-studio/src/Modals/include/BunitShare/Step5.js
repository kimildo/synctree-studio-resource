import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import StepBottom from './StepBottom';
@inject('shareStore')
@observer
class Step5 extends Component {
    componentDidMount() {
        // const url = this.props.shareStore.getShareUrl;
        // if (url === '') {
        this.props.shareStore.callSharedUrl();
        // }
    }
    render() {
        const url = this.props.shareStore.getShareUrl;
        return (
            <>
                {url !== '' ? (
                    <>
                        <h5>
                            아래의 URL과 암호를 셋팅할 수 있는 접속 url이
                            전달됩니다.
                        </h5>
                        <p>
                            아래의 url을 담당 파트너 개발자분에게 전달 부탁
                            드립니다.
                        </p>
                        <div className="well well-sm break-word">{url}</div>
                        <p className="txt-right">
                            <button
                                type="button"
                                className="btn btn-default copy-btn"
                                data-clipboard-text={url}>
                                <i className="fa fa-copy" /> Copy
                            </button>
                        </p>
                        <StepBottom />
                    </>
                ) : (
                    'Loading...'
                )}
            </>
        );
    }
}

export default Step5;
