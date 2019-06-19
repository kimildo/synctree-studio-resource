import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
@inject('navStore')
@observer
class Nav extends Component {
    render() {
        const nav = this.props.navStore.getNav;
        return (
            <ol className="breadcrumb" id="top_page_title">
                <li>Home</li>
                {nav.map((navstr, i) => (
                    <li key={`nav_${i}`}>{navstr}</li>
                ))}
            </ol>
        );
    }
}

export default Nav;
