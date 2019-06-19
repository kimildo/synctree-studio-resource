import React, { Component } from 'react';
import _ from 'lodash';
import { inject, observer } from 'mobx-react';
import BtnAsyncInc from './BtnAsyncInc';

@inject('bizStore', 'opsStore')
@observer
class BtnAsync extends Component {
    handleChange = e => {
        const { data } = this.props;

        this.props.bizStore.setAsyncIdxs(data.binding_seq, e.target.checked);
    };
    handleClick = checked => {
        const { data } = this.props;

        this.props.bizStore.setAsyncIdxs(data.binding_seq, checked);
    };
    render() {
        const { data, bizStore } = this.props,
            seq = data.binding_seq;
        // console.log('BtnAsync', data, bizStore.asyncIdxs.toJS());

        let start = 0,
            end = 2;

        switch (bizStore.asyncIdxs.length) {
            case 1:
                start = _.includes(bizStore.asyncIdxs, seq) ? 1 : 2;
                end = 0;
                break;
            case 2:
                start = _.head(bizStore.asyncIdxs) === seq ? 3 : 2;
                end = _.last(bizStore.asyncIdxs) === seq ? 3 : 2;
                break;
            default:
                break;
        }

        return (
            <>
                <span className="async-text">{data.op_text}</span>{' '}
                <BtnAsyncInc
                    start={start}
                    end={end}
                    handleClick={this.handleClick.bind(this)}
                />
            </>
        );
    }
}

export default BtnAsync;
