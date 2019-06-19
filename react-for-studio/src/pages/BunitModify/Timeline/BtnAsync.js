import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
@inject('bizStore')
@observer
class BtnAsync extends Component {
    setAsyncMode = () => {
        this.props.bizStore.setAsyncMode();
    };
    render() {
        const { asyncMode, bizInfo } = this.props.bizStore;

        const hasAsync =
            !!bizInfo.async_bind_seq || bizInfo.operators.length === 0;
        return (
            <>
                {!hasAsync ? (
                    <button
                        className={`btn btn-default btn-block ${
                            !!asyncMode ? 'btn-success' : 'btn-default'
                        }`}
                        onClick={this.setAsyncMode}>
                        Async {!!asyncMode ? 'Cancel' : ''}
                    </button>
                ) : (
                    ''
                )}
            </>
        );
    }
}

export default BtnAsync;
