import React, { Component } from 'react';
import { observer } from 'mobx-react';
@observer
class TimelineLoop extends Component {
    render() {
        const { line_title } = this.props.row;

        return (
            <li className="smart-timeline lifeline">
                <div className="smart-timeline-list">
                    <div className="smart-timeline-icon ">
                        <span className="node-title" title={`${line_title}`}>
                            <span className="node-title-text">
                                {line_title}
                            </span>
                        </span>

                        {/* <ul className="dropdown-menu">
                            <li>
                                <a
                                    title="Set Name"
                                    className="btn-no-action btn_set_node_name"
                                    onClick={this.setTitle.bind(this)}>
                                    <i className="glyphicon glyphicon-edit" />
                                    Set Title
                                </a>
                            </li>
                        </ul> */}
                    </div>
                    <div className="smart-timeline-content" />
                </div>
            </li>
        );
    }
}
export default TimelineLoop;
