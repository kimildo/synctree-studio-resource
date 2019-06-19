import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Droppable } from 'react-beautiful-dnd';
import Collapse, { Panel } from 'rc-collapse';
import _ from 'lodash';

import AccordionMenuItem from './AccordionMenuItem';
import BtnAsync from './BtnAsync';

const getListStyle = isDraggingOver => ({
    background: isDraggingOver ? 'lightblue' : 'lightgrey',
    padding: 8,
    marginTop: 5,
});

const getItem = (items, type) => {
    return items.map((row, i) => {
        const key =
            type === 'op' ? `drag_op_w_${row.op_id}` : `drag_${row.type}`;
        return <AccordionMenuItem item={row} index={i} key={key} type={type} />;
    });
};

@inject('opsStore', 'modalStore', 'bizStore')
@observer
class AccordionMenu extends Component {
    getOps() {
        const items = this.props.opsStore.deselected,
            returnData = [],
            groupedItems = {};
        _.forEach(items, item => {
            if (typeof groupedItems[item.op_ns_name] === 'undefined') {
                groupedItems[item.op_ns_name] = [];
            }
            groupedItems[item.op_ns_name].push(item);
        });

        _.forEach(groupedItems, (value, key) => {
            returnData.push(
                <Panel
                    header={key || 'No Name'}
                    key={`AccordionMenuItemGroup_${key}`}>
                    <Droppable droppableId="droppable_op">
                        {(provided, snapshot) => (
                            <div
                                ref={provided.innerRef}
                                style={getListStyle(snapshot.isDraggingOver)}
                                {...provided.droppableProps}>
                                {getItem(value, 'op')}
                                {provided.placeholder}
                            </div>
                        )}
                    </Droppable>
                </Panel>
            );
        });
        return returnData;
    }
    createOperator() {
        this.props.modalStore.showModal({
            type: 'EditOperator',
            data: {
                type: 'create',
            },
        });
    }
    componentDidMount() {
        this.props.opsStore.getOperators();
    }

    render() {
        const controlList = [
            {
                type: 'alt',
                text: 'Alt',
            },
            {
                type: 'loop',
                text: 'Loop',
            },
        ];

        return (
            <div className="timeline-acc">
                <Collapse accordion={true} defaultActiveKey={'op'}>
                    <Panel header={`Op List`} key={'op'}>
                        <button
                            className="btn btn-default btn-block"
                            onClick={this.createOperator.bind(this)}>
                            <i className="glyphicon glyphicon-plus" /> New
                        </button>{' '}
                        <Collapse accordion={true}>{this.getOps()}</Collapse>
                    </Panel>
                    <Panel header={'Control List'}>
                        <Droppable droppableId="droppable_control">
                            {(provided, snapshot) => (
                                <div
                                    ref={provided.innerRef}
                                    style={getListStyle(
                                        snapshot.isDraggingOver
                                    )}
                                    {...provided.droppableProps}>
                                    {getItem(controlList, 'control')}
                                    {provided.placeholder}
                                </div>
                            )}
                        </Droppable>
                        <BtnAsync />{' '}
                    </Panel>
                </Collapse>
            </div>
        );
    }
}

export default AccordionMenu;
