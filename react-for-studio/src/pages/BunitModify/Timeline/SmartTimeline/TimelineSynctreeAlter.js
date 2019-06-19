import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import TimelineSynctreeOpInfo from './TimelineSynctreeOpInfo';
import Util from '../../../../library/utils/Util';
/* global global_data */

@inject('modalStore', 'alterStore')
@observer
class TimelineSynctreeAlter extends Component {
    editAlt = (row, prevOpId, targetControll) => {
        this.props.modalStore.showModal({
            type: 'Alternative',
            data: {
                ops: row,
                lastOp: prevOpId,
                base: targetControll,
            },
        });
    };
    unbindAlter = row => {
        const { dictionary } = global_data;
        Util.confirmMessage(
            `${dictionary.alert.warn}!`,
            dictionary.alert.ask
        ).then(() => {
            this.props.alterStore.unbindAlter(row);
        });
    };

    render() {
        const { bizInfo, row, lines, prevOpId } = this.props;
        let maxIndex = 0;
        // l = row.length - 1;

        let targetControll = _.find(bizInfo.controlls, {
            control_alt_id: parseInt(row.data[0].controll_info.control_id),
        });

        let alter = row.data.map((r, i) => {
            const subIndex = _.findIndex(lines, {
                line_idx: r.target_line_idx,
            });
            if (maxIndex < subIndex) {
                maxIndex = subIndex;
            }

            return (
                <TimelineSynctreeOpInfo
                    row={r}
                    prevOpIdx={prevOpId}
                    opIdx={r.op_id}
                    bindingSeq={r.binding_seq}
                    index={subIndex + 1}
                    controllInfo={{
                        my: r.controll_info,
                        base: targetControll,
                    }}
                    key={`TimelineSynctreeOpInfo_${i}_${r.op_id}`}
                />
            );
        });

        return (
            <div
                className="alter"
                style={{
                    width: (maxIndex + 1) * 10 + 'vw',
                }}>
                {alter}
                <div className="text-right alter-btns">
                    <button
                        type="button"
                        className="btn btn-labeled btn-info"
                        onClick={this.editAlt.bind(
                            this,
                            row.data,
                            prevOpId,
                            targetControll
                        )}>
                        {' '}
                        <span className="btn-label">
                            <i className="glyphicon glyphicon-wrench" />
                        </span>
                        Edit{' '}
                    </button>{' '}
                    <button
                        type="button"
                        className="btn btn-labeled btn-danger"
                        onClick={this.unbindAlter.bind(this, row.data)}>
                        {' '}
                        <span className="btn-label">
                            <i className="glyphicon glyphicon-trash" />
                        </span>
                        Delete{' '}
                    </button>
                </div>
            </div>
        );
    }
}

export default TimelineSynctreeAlter;
