import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Util from '../../../library/utils/Util';
@inject('shareStore')
@observer
class StepBottom extends Component {
    prev() {
        this.props.shareStore.prev();
    }
    next() {
        if (this.props.from) {
            if (Util.formCheckRequired(this.props.from)) {
                this.props.shareStore.next();
            }
        } else {
            this.props.shareStore.next();
        }
    }
    render() {
        return (
            <p className="text-right">
                <button
                    type="button"
                    onClick={this.prev.bind(this)}
                    className="btn btn-primary prev-step">
                    <i className="fa fa-arrow-left" /> 이전
                </button>{' '}
                <button
                    type="button"
                    onClick={this.next.bind(this)}
                    className="btn btn-primary next-step">
                    <i className="fa fa-arrow-right" /> 다음
                </button>
            </p>
        );
    }
}
export default StepBottom;
