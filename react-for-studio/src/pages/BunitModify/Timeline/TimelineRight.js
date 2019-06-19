import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import _ from 'lodash';
import TimelineSynctree from './SmartTimeline/TimelineSynctree';
import TimelineLoop from './SmartTimeline/TimelineLoop';

@inject('bizStore', 'modalStore')
@observer
class TimelineRight extends Component {
    getLoopTimeline() {
        const { bizInfo } = this.props.bizStore;
        if (!!bizInfo.lines) {
            // return bizInfo.lines.map((value, key) => {
            const returnData = [];
            _.forEach(bizInfo.lines, (value, key) => {
                // console.log('getLoopTimeline', value, key);
                returnData.push(
                    <TimelineLoop
                        row={value}
                        line_id={key}
                        key={`TimelineLoop_${key}`}
                    />
                );
            });
            return returnData;
        }
        return '';
    }

    render() {
        const loopTimeline = this.getLoopTimeline();

        return (
            <div id="timeline_right">
                <div className="well well-sm">
                    <ul className="timeline-wrapper">
                        <li
                            className="smart-timeline timeline-synctree"
                            style={{ height: this.props.h }}>
                            <TimelineSynctree />
                        </li>
                        {loopTimeline}
                    </ul>
                </div>
            </div>
        );
    }
}
export default TimelineRight;
