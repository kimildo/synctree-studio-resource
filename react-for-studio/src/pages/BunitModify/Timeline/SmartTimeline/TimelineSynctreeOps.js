import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import TimelineSynctreeOpInfo from './TimelineSynctreeOpInfo';
import TimelineSynctreeAlter from './TimelineSynctreeAlter';
import TimelineSynctreeAsync from './TimelineSynctreeAsync';

@inject('bizStore')
@observer
class TimelineSynctreeOps extends Component {
    unsetAsync = () => {
        this.props.bizStore.unsetAsync();
    };
    render() {
        const { bizInfo } = this.props.bizStore;
        const lines = Object.values(bizInfo.lines).sort(
            (a, b) => a.line_idx - b.line_idx
        );

        let prevOpId = null;

        return bizInfo.operators.map((row, i) => {
            const index = _.findIndex(lines, {
                line_idx: row.data.target_line_idx,
            });

            switch (row.type) {
                case 'normal':
                default:
                    let normal = (
                        <TimelineSynctreeOpInfo
                            row={row.data}
                            prevOpIdx={prevOpId}
                            opIdx={row.data.op_id}
                            bindingSeq={row.data.binding_seq}
                            controllInfo={null}
                            index={index + 1}
                            key={`TimelineSynctreeOpInfo_${row.data.op_id}`}
                        />
                    );
                    prevOpId = row.data.op_id;
                    return normal;
                case 'alter':
                    return (
                        <TimelineSynctreeAlter
                            bizInfo={bizInfo}
                            row={row}
                            lines={lines}
                            prevOpId={prevOpId}
                            key={`TimelineSynctreeAlter_${i}`}
                        />
                    );

                case 'async':
                    return (
                        <TimelineSynctreeAsync
                            row={row}
                            lines={lines}
                            prevOpId={prevOpId}
                            key={`TimelineSynctreeAsync_${i}`}
                        />
                    );
            }
        });
    }
}

export default TimelineSynctreeOps;
