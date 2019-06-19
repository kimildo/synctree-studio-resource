import React from 'react';

const Wrapper = ({ children }) => {
    return (
        <section>
            <div className="inbox-nav-bar no-content-padding">
                <h1 className="page-title txt-color-blueDark hidden-tablet">
                    Operator
                </h1>
            </div>
            <div className="inbox-body no-content-padding">{children}</div>
        </section>
    );
};

export default Wrapper;
