import React, { Component } from 'react';
import Top from './include/Top';
import Bottom from './include/Bottom';
import Aside from './include/Aside';
import Nav from './include/Nav';

class WithLeft extends Component {
    render() {
        return (
            <div className="app-main">
                <Top />
                <Aside />
                <div id="main" role="main">
                    <div id="wrapper">
                        <div id="notice">
                            [공지] 공지사항 텍스트가 들어갑니다.
                        </div>
                        <div id="ribbon">
                            {/* <!-- breadcrumb --> */}
                            <Nav />
                            {/* <!-- end breadcrumb --> */}
                        </div>

                        {/* <!-- MAIN CONTENT --> */}
                        <div id="content">{this.props.children}</div>
                        {/* <!-- END MAIN CONTENT --> */}
                    </div>
                </div>
                <Bottom />
            </div>
        );
    }
}

export default WithLeft;
