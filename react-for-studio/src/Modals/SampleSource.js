import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Highlight from 'react-highlight.js';
import _ from 'lodash';
import ModalWrapper from './wrapper/ModalWrapper';
import ModalHeader from './wrapper/ModalHeader';
import ModalFooter from './wrapper/ModalFooter';
import Request from '../library/utils/Request';

@inject('modalStore', 'userInfoStore')
@inject('bizStore')
@observer
class SampleSource extends Component {
    constructor(props) {
        super(props);
        this.request = Request;
        this.sampleCodeTypes = this.props.bizStore.getSampleCodeTypes;
        this.state = {
            lang: null,
            code: '',
        };
        this.codeLayer = React.createRef();
    }

    getSampleCode(snipet) {
        const { bizInfo } = this.props.bizStore;

        this.request
            .post('/console/apps/bunit/getSampleCodes', {
                app_id: parseInt(bizInfo.app_id),
                biz_id: bizInfo.biz_id,
                snipet: snipet,
            })
            .then(res => res.data.data)
            .then(data => {
                this.setState({
                    lang: this.sampleCodeTypes[snipet],
                    code: data.code,
                });
            })
            .catch(err => {
                /*...handle the error...*/
                console.log('getSampleCode error', err);
            });
    }

    getLanguages() {
        const returnData = [];
        const _this = this;
        _.forOwn(this.sampleCodeTypes, (value, key) => {
            returnData.push(
                <li key={`getLanguages_${key}`}>
                    <a
                        className="set-sample-language"
                        onClick={this.getSampleCode.bind(_this, key)}
                        data-snippet={key}>
                        {value}
                    </a>
                </li>
            );
        });
        return returnData;
    }
    getLangType() {
        if (this.state.lang) {
            let l = this.state.lang.toLowerCase();
            switch (l) {
                case 'jquery':
                case 'react':
                    return 'javascript';
                default:
                    return l;
            }
        }
        return '';
    }
    render() {
        const langs = this.getLanguages();
        const lType = this.getLangType();
        return (
            <ModalWrapper>
                <div className="modal-content">
                    <ModalHeader>
                        <i className="fa fa-lg fa-fw fa-terminal" /> Sample Code
                        - Example Request
                    </ModalHeader>
                    <div className="modal-body">
                        <div className="widget-body padding-15 margin-top-10">
                            <div className="row">
                                <div className="col-md-12">
                                    <a
                                        className="btn btn-default btn-top dropdown-toggle padding-top-10 block-btn"
                                        data-toggle="dropdown"
                                        aria-expanded="true">
                                        {this.state.lang || 'Select Language'}
                                    </a>
                                    <ul className="dropdown-menu code-select">
                                        {langs}
                                    </ul>
                                </div>
                            </div>
                            <div className="row">
                                <Highlight className="m-10" language={lType}>
                                    {this.state.code}
                                </Highlight>
                            </div>
                        </div>
                    </div>
                    <ModalFooter>
                        <button
                            type="button"
                            className="btn btn-default copy-btn"
                            data-clipboard-text={this.state.code}>
                            <i className="fa fa-copy" /> Copy
                        </button>
                    </ModalFooter>
                </div>
            </ModalWrapper>
        );
    }
}

export default SampleSource;
