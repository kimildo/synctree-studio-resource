import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';

@inject('bizStore')
@observer
class Arrows extends Component {
    render() {
        const { isDragging } = this.props.bizStore,
            { index } = this.props,
            lineStyle = {
                width: index * 10 + 'vw',
                minWidth: index * 171,
            };
        return (
            <>
                {!isDragging ? (
                    <div className="flow-arrow">
                        <div className="timeline-static-request">
                            <div className="line" style={lineStyle} />
                        </div>
                        <div className="timeline-static-response">
                            <div className="line" style={lineStyle} />
                            <div className="arrow">◀</div>
                        </div>
                    </div>
                ) : (
                    ''
                )}
            </>
        );
    }
}

export default Arrows;
