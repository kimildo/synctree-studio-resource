import React, { Component } from 'react';
import Top from './include/Top';
import Bottom from './include/Bottom';

class NoLeft extends Component {
    render() {
        const noHeader = this.props.noHeader || false;
        const noFooter = this.props.noFooter || false;
        return (
            <>
                {/* <div className="app-main"> */}
                {!noHeader ? <Top /> : ''}
                {this.props.children}
                {!noFooter ? <Bottom /> : ''}

                {/* </div> */}
            </>
        );
    }
}

export default NoLeft;
