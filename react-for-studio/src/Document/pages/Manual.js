import React, { Component } from 'react';
import { inject, observer } from 'mobx-react';
import Operators from '../include/manual/Operators';
import SecureProtocol from '../include/manual/SecureProtocol';

import Parameters from '../include/params/Parameters';

@inject('docsStore')
@observer
class Manual extends Component {
    state = {
        openLang: false,
    };

    activeLang() {
        this.setState(prevState => ({
            openLang: !!prevState.openLang ? false : true,
        }));
    }
    changeLang(lang) {
        this.props.docsStore.changeLang(lang);
        this.setState({ openLang: false });
    }
    getLangList(langs) {
        const keys = Object.keys(langs);
        return keys.map(l => (
            <li key={`lang_${l}`} onClick={this.changeLang.bind(this, l)}>
                {l}
            </li>
        ));
    }
    render() {
        const data = this.props.docsStore.getData,
            { language, langList } = this.props.docsStore,
            { openLang } = this.state;
        return (
            <div id={'top'} className="sectionWrap">
                <section className="docBody">
                    <div className="leftBody">
                        <div className="mainTxt">
                            <h1>{data.biz_name} - Studio</h1>
                            {/* <h2>Studio 백엔드 메뉴얼 입니다.</h2> */}
                            <h3>Description</h3>
                            <ul>
                                <li>{data.biz_desc || '-'}</li>
                                <li>
                                    By default, the response data type is{' '}
                                    <strong>json</strong>.
                                </li>
                            </ul>
                            <h3>End Point</h3>
                            <ul>
                                <li>
                                    {/* <a href="https://dev.studio.synctreengine.com">
                                        https://dev.studio.synctreengine.com
                                    </a>
                                    - Ntuple Portal Development */}
                                    {/* copy url 버튼 추가?? */}
                                    <a
                                        className="end-point"
                                        href={data.product_end_point}
                                        target="_blank"
                                        rel="noopener noreferrer">
                                        {data.product_end_point}
                                    </a>
                                </li>
                            </ul>
                            <h3 id={'request'}>Request Method</h3>
                            <ul>
                                <li>
                                    {data.req_method === 1 ? (
                                        <span className="FD_H_color_get">
                                            GET
                                        </span>
                                    ) : (
                                        <span className="FD_H_color_post">
                                            POST
                                        </span>
                                    )}
                                </li>
                            </ul>
                            <h3>Request Parameters</h3>
                            <Parameters
                                params={data.request}
                                type={'req'}
                                id={'biz_req'}
                            />
                        </div>
                    </div>

                    <div className="rightBody">
                        <div className="langSelect">
                            <p>Language</p>
                            <div className="langList">
                                <button onClick={this.activeLang.bind(this)}>
                                    {language}
                                    <span />
                                </button>
                                {openLang ? (
                                    <ul>{this.getLangList(langList)}</ul>
                                ) : (
                                    ''
                                )}
                            </div>
                        </div>
                    </div>
                </section>
                <section id={'ops'} className="docBody_Fname">
                    <div className="leftBody_Fname">
                        <div className="folderDetailName">
                            <p className="folderName">Included Operators</p>
                        </div>
                    </div>
                </section>
                <Operators />
                <SecureProtocol end_point={data.get_command_end_point} />
            </div>
        );
    }
}

export default Manual;
