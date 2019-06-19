import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
@inject('modalStore', 'shareStore')
@observer
class Step1 extends Component {
    setShareOp(op) {
        this.props.shareStore.setShareOp(op.op_id);
    }
    getOperatorBtns() {
        const { operators } = this.props.biz;
        return operators.map(op => {
            return (
                <button
                    key={`ex_st1_op_${op.op_id}`}
                    onClick={this.setShareOp.bind(this, op)}
                    className="list-group-item">
                    {op.op_info.op_name}
                    <span className="badge">Share</span>
                </button>
            );
        });
    }
    render() {
        const { biz } = this.props;
        const btns = this.getOperatorBtns();

        return (
            <>
                <h5>
                    Last Build Version :{' '}
                    <span>
                        {biz.last_build.biz_ops_version} (
                        {biz.last_build.build_date})
                    </span>
                </h5>
                <label>Setted Url</label>
                <div className="well well-sm break-word">{biz.end_point}</div>
                <label>Shareable Operation List</label>
                <div className="list-group">{btns}</div>
            </>
        );
    }
}

export default Step1;
