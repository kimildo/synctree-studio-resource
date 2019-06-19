import React, { Component } from 'react';
import NoLeft from './NoLeft';

class LayoutAuth extends Component {
    render() {
        return (
            <NoLeft noFooter={true} noHeader={true}>
                {/* // <NoLeft> */}
                <div className="auth">
                    <div id="headers" />
                    <div id="container">
                        <div className="outer">
                            <div className="inner">
                                <div className="title">
                                    <img
                                        src="/htdocs/img/title@3x.png"
                                        alt="Synctree Studio Ver Image"
                                    />
                                </div>
                                <div className="auth-wrapper">
                                    <div className="section">
                                        <p align="middle">
                                            {/* {# youtube player 크기 조정 필요 #} */}
                                            <iframe
                                                title="yt-player"
                                                id="ytplayer"
                                                type="text/html"
                                                src="//www.youtube.com/embed/TPbKyD2bAR4?autoplay=1&origin={{ domain }}"
                                                frameBorder="0"
                                            />
                                        </p>
                                    </div>
                                    {this.props.children}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="footer">
                        <p className="office">
                            Synctree Studio V2.1 © Ntuple since 2018
                        </p>
                    </div>
                </div>
            </NoLeft>
        );
    }
}

export default LayoutAuth;
