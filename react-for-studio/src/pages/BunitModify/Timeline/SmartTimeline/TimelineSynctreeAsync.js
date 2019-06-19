import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import TimelineSynctreeOpInfo from './TimelineSynctreeOpInfo';

@inject('bizStore')
@observer
class TimelineSynctreeAsync extends Component {
    unsetAsync = () => {
        this.props.bizStore.unsetAsync();
    };
    render() {
        const { row, lines, prevOpId } = this.props;
        let maxIndexAsync = 0;
        let async = row.data.map((r, i) => {
            const subIndex = _.findIndex(lines, {
                line_idx: r.target_line_idx,
            });
            if (maxIndexAsync < subIndex) {
                maxIndexAsync = subIndex;
            }

            return (
                <TimelineSynctreeOpInfo
                    row={r}
                    prevOpIdx={prevOpId}
                    opIdx={r.op_id}
                    bindingSeq={r.binding_seq}
                    controllInfo={null}
                    index={subIndex + 1}
                    key={`TimelineSynctreeOpInfo_${i}_${r.op_id}`}
                />
            );
        });
        return (
            <div
                className="async"
                style={{
                    width: (maxIndexAsync + 1) * 10 + 'vw',
                }}>
                {async}
                <div className="text-right alter-btns">
                    <button
                        type="button"
                        className="btn btn-labeled btn-danger"
                        onClick={this.unsetAsync.bind(this, row.data)}>
                        {' '}
                        <span className="btn-label">
                            <i className="glyphicon glyphicon-trash" />
                        </span>
                        Unset{' '}
                    </button>
                </div>
            </div>
        );
    }
}
export default TimelineSynctreeAsync;
