import React, { PureComponent } from 'react';
import { createPortal } from 'react-dom';
import { Draggable } from 'react-beautiful-dnd';

const _dragEl = document.getElementById('draggable');
// using some little inline style helpers to make the app look okay(보기좋게 앱을 만드는 인라인 스타일 헬퍼)
const getItemStyle = (draggableStyle, isDragging) => ({
    // some basic styles to make the items look a bit nicer(아이템을 보기 좋게 만드는 몇 가지 기본 스타일)

    // change background colour if dragging(드래깅시 배경색 변경)
    background: isDragging ? 'lightblue' : 'white',

    // styles we need to apply on draggables(드래그에 필요한 스타일 적용)
    ...draggableStyle,
});

class AccordionMenuItem extends PureComponent {
    optionalPortal(styles, element) {
        if (styles.position === 'fixed') {
            return createPortal(element, _dragEl);
        }
        return element;
    }
    render() {
        const { item, index, type } = this.props;
        const key = type === 'op' ? `drag_op_${item.op_id}` : item.type,
            dId = type === 'op' ? item.op_id : item.type,
            txt = type === 'op' ? item.op_name : item.text;

        return (
            <Draggable key={key} index={index} draggableId={dId}>
                {(provided, snapshot) => {
                    return (
                        <div>
                            {this.optionalPortal(
                                provided.draggableProps.style,
                                <div
                                    ref={provided.innerRef}
                                    className="drag-item"
                                    style={getItemStyle(
                                        provided.draggableProps.style,
                                        snapshot.isDragging
                                    )}
                                    {...provided.draggableProps}
                                    {...provided.dragHandleProps}>
                                    {txt}
                                </div>
                            )}
                            {provided.placeholder}
                        </div>
                    );
                }}
            </Draggable>
        );
    }
}

export default AccordionMenuItem;
