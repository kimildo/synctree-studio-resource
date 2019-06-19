import React, { Component } from 'react';

import MenuItem from './MenuItem';

class MenuGroup extends Component {
    state = { open: false };
    getItem = ops => {
        return ops.map(op => (
            <MenuItem key={`l_o_${op.operation_id}`} op={op} />
        ));
    };
    toggleFolder = () => {
        const { open } = this.state;
        this.setState({ open: !open });
    };
    render() {
        const { namespace, ops } = this.props,
            { open } = this.state;
        return (
            <li className={`folder ${open ? 'open' : ''}`}>
                <div className="folderTitle">
                    <a onClick={this.toggleFolder}>
                        <span className="FAimg" />

                        <span className="Fimg" />
                        <span className="Ftxt">{namespace}</span>
                    </a>
                </div>
                {open ? (
                    <ul className="folderGroup">{this.getItem(ops)}</ul>
                ) : (
                    ''
                )}
            </li>
        );
    }
}

export default MenuGroup;
