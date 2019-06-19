import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Droppable } from 'react-beautiful-dnd';
import Util from '../../../../library/utils/Util';
/* global global_data */

import TimelineSynctreeOps from './TimelineSynctreeOps';

const getListStyle = (isDraggingOver, isDragging) => ({
    background: isDraggingOver
        ? '#add8e67d'
        : isDragging
        ? '#ff7f507d'
        : '#d3d3d37d',
    padding: 8,
    minHeight: 500,
    position: 'absolute',
    left: -14,
    top: 0,
    width: '10vw',
});
@inject('bizStore', 'modalStore', 'alterStore')
@observer
class TimelineSynctree extends Component {
    editAlt = (data, opId, baseData) => {
        this.props.modalStore.showModal({
            type: 'Alternative',
            data: {
                ops: data,
                lastOp: opId,
                base: baseData,
            },
        });
    };
    unbindAlter = data => {
        const { dictionary } = global_data;
        Util.confirmMessage(
            `${dictionary.alert.warn}!`,
            dictionary.alert.ask
        ).then(() => {
            this.props.alterStore.unbindAlter(data);
        });
    };

    addFlow() {
        this.props.modalStore.showModal({
            type: 'Operators',
            data: {
                type: 'getData',
            },
        });
    }
    render() {
        const isDragging = this.props.bizStore.isDragging;
        return (
            <div className="smart-timeline-list">
                <div className="smart-timeline-icon txt-color-blue">
                    SYNCTREE
                </div>
                <div className="smart-timeline-content">
                    <Droppable droppableId="droppable_tree">
                        {(provided, snapshot) => (
                            <div
                                ref={provided.innerRef}
                                style={getListStyle(
                                    snapshot.isDraggingOver,
                                    isDragging
                                )}
                                {...provided.droppableProps}>
                                <TimelineSynctreeOps
                                    editAlt={this.editAlt}
                                    unbindAlter={this.unbindAlter}
                                />
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </div>
            </div>
        );
    }
}

export default TimelineSynctree;
