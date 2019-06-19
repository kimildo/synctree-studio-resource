import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import { Helmet } from 'react-helmet';
import { DragDropContext } from 'react-beautiful-dnd';
import _ from 'lodash';

import TimelineLeft from './TimelineLeft';
import TimelineRight from './TimelineRight';
import AccordionMenu from './AccordionMenu';

import Util from '../../../library/utils/Util';

@inject('navStore', 'opsStore', 'bizStore', 'modalStore', 'alterStore')
@observer
class Timeline extends Component {
    componentDidMount() {
        this.props.navStore.setNav(['Apps', 'biz Unit', 'Timeline']);
        if (!this.props.alterStore.controlOperator) {
            this.props.alterStore.getControllOperators();
        }
    }
    componentWillUnmount() {
        this.props.bizStore.unsetAsyncMode();
        this.props.bizStore.setDragging(false);
    }
    onDragStart(id, location) {
        //console.log('onDragStart', id, location);
        this.props.bizStore.setDragging(true);
    }

    onDropAction = dId => {
        switch (dId) {
            case 'alt': // alt
                this.props.modalStore.showModal({
                    type: 'Alternative',
                });
                break;
            case 'loop': // loop
                Util.showSmallBox('error_message', 1000, '준비중입니다.');
                break;
            default:
                return;
        }
    };
    addOpAction = op_id => {
        const op = _.find(this.props.opsStore.deselected, {
            op_id: op_id,
        });
        if (!op) return false;

        switch (op.auth_type_code) {
            case 0:
            default:
                // 그냥 등록
                this.props.opsStore.moveOperatorsByIndex('bind', [op_id]);

                break;
            case 1: // env, username, password 배열로 넘김
            case 2: // env, auth_key 배열로 넘김
                this.props.modalStore.showModal({
                    type: 'BindOpAddAuth',
                    data: {
                        op_id: op_id,
                        type: op.auth_type_code,
                    },
                });
                break;
        }

        // console.log('addOpAction', op);
        //auth_type_code에 따른 action
    };
    onDragEnd(result) {
        console.log('onDragEnd', result);
        const { source, destination, draggableId } = result;
        this.props.bizStore.setDragging(false);
        if (!destination) {
            return;
        }
        if (destination.droppableId !== 'droppable_tree') {
            return;
        }

        switch (source.droppableId) {
            case 'droppable_op':
                this.addOpAction(draggableId);
                break;
            case 'droppable_control':
                this.onDropAction(draggableId);
                break;
            default:
                return;
        }
    }
    render() {
        let cnt = 0;
        _.forEach(this.props.bizStore.bizInfo.operators, (value, key) => {
            if (!value.length) {
                cnt++;
            } else {
                cnt += value.length;
            }
        });
        let h = 120 * cnt;
        return (
            <>
                <Helmet>
                    <title>Timeline - Synctree Studio V2.0 </title>
                </Helmet>
                <DragDropContext
                    onDragStart={this.onDragStart.bind(this)}
                    onDragEnd={this.onDragEnd.bind(this)}>
                    <AccordionMenu />
                    <TimelineLeft h={h} />
                    <TimelineRight h={h} />
                </DragDropContext>
            </>
        );
    }
}

export default Timeline;
