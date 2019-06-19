import React from 'react';

const SecureProtocol = ({ end_point }) => (
    <section className="docBody_FD" id="secureProtocol">
        <div className="leftBody_FD">
            <div className="mainTxt_FD">
                <h3 className="FD_head_txt">
                    <span className="FD_H_color_post">POST</span> Secure
                    Protocol
                </h3>
                <div className="FD_H_url">{end_point}</div>
                <h3>DESCRIPTION</h3>
                <p className="FD_des_txt">
                    The stored data is extracted by the issued key.
                </p>

                <div className="headerInfo">
                    <p className="HI_title">HEADERS</p>
                    <div className="HI_dubleP BI_normalP">
                        <p className="HI_subtitle">Content-Type</p>
                        <p className="HI_subdetail">
                            <span className="ST_E">application/json</span>
                        </p>
                    </div>
                </div>

                <div className="bodyInfo">
                    <p className="BI_title">REQUEST PARAMETERS</p>
                    <div className="BI_dubleP">
                        <p className="BI_subtitle">
                            <span className="ST_E">event_key</span>
                            <span className="ST_K required">(Required)</span>
                        </p>
                        <p className="BI_subdetail">
                            <span className="ST_E">
                                Event key received as parameter
                            </span>
                            <br />
                            <span className="ST_K">STR</span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
        <div className="rightBody_FD" />
    </section>
);

export default SecureProtocol;
