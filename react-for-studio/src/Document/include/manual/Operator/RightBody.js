import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Highlight from 'react-highlight.js';
import Modal from 'react-modal';
import ClipboardJS from 'clipboard';
import _ from 'lodash';

import customStyle from '../../../../Modals/customStyle';

@inject('docsStore')
@observer
class RightBody extends Component {
    state = { expand: false, copied: false, modalOpen: false };
    codeLayer = React.createRef();
    clipboard = new ClipboardJS('.FC_copybtn');

    componentDidMount() {
        const { code } = this.codeLayer.current.refs;
        if (code.clientHeight > 200) {
            this.setState({ expand: true });
        }
        this.clipboard.on('success', e => {
            this.setState({ copied: true });
            setTimeout(() => {
                this.setState({ copied: false });
            }, 1000);

            e.clearSelection();
        });
        this.clipboard.on('error', e => {
            console.error('Action:', e.action);
            console.error('Trigger:', e.trigger);
        });
        code.closest('pre').addEventListener('click', () => {
            this.setState({ modalOpen: true });
        });
    }
    componentWillUnmount() {
        this.clipboard.destroy();
    }

    closeModal = () => this.setState({ modalOpen: false });

    render() {
        const { code, type } = this.props,
            { expand, copied, modalOpen } = this.state;

        return (
            <div className="rightBody_FD">
                <div className="folderCode">
                    <p className="FC_name">Sample Code</p>
                    {/* <p className="FC_route">200 - OK</p> */}
                    <div className="FC_moreCode">
                        {!copied ? (
                            <button
                                className="FC_copybtn"
                                data-clipboard-text={code}>
                                Copy to Clipboard
                            </button>
                        ) : (
                            <span className="FC_copyClick">Copied</span>
                        )}

                        <Highlight
                            className={`codeView FC_codeView ${
                                !!expand ? 'codeExpendable' : ''
                            }`}
                            ref={this.codeLayer}
                            language={type}>
                            {code}
                        </Highlight>
                    </div>
                </div>
                {/* <!--folder_subCode--> */}
                <Modal
                    isOpen={modalOpen}
                    onRequestClose={this.closeModal}
                    ariaHideApp={false}
                    style={customStyle}
                    contentLabel="Sample Code">
                    <span className="close" onClick={this.closeModal}>
                        &times;
                    </span>
                    <div className="FC_moreCode">
                        {!copied ? (
                            <button
                                className="FC_copybtn"
                                data-clipboard-text={code}>
                                Copy to Clipboard
                            </button>
                        ) : (
                            <span className="FC_copyClick">Copied</span>
                        )}
                    </div>
                    <Highlight language={type}>{code}</Highlight>
                </Modal>
            </div>
        );
    }
}

export default RightBody;
