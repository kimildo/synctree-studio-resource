import React from 'react';
const Error404 = () => (
    <div id="content">
        <div className="row">
            <div className="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                <div className="row">
                    <div className="col-sm-12">
                        <div className="text-center error-box">
                            <h1 className="error-text-2 bounceInDown animated">
                                {' '}
                                Error 404{' '}
                                <span className="particle particle--c" />
                                <span className="particle particle--a" />
                                <span className="particle particle--b" />
                            </h1>
                            <h2 className="font-xl">
                                <strong>
                                    <i className="fa fa-fw fa-warning fa-lg text-warning" />{' '}
                                    Page <u>Not</u> Found
                                </strong>
                            </h2>
                            <p>home</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
);
export default Error404;
