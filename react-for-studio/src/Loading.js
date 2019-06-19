import React from 'react';
import ReactLoading from 'react-loading';

const Loading = props => {
    // if (props.error) {
    //     // When the loader has errored
    //     return <div>Error! <button onClick={props.retry}>Retry</button></div>;
    // } else if (props.timedOut) {
    //     // When the loader has taken longer than the timeout
    //     return <div>Taking a long time... <button onClick={props.retry}>Retry</button></div>;
    // } else if (props.pastDelay) {
    //     // When the loader has taken longer than the delay
    //     return <div>Loading...</div>;
    // } else {
    //     // When the loader has just started
    //     return null;
    // }
    if (props.error) {
        console.error('Loading Error', props);
        return (
            <div>
                Error Fired!{' '}
                <button
                    className="btn btn-labeled btn-lg btn-danger"
                    onClick={props.retry}>
                    <span className="btn-label">
                        <i className="glyphicon glyphicon-refresh" />
                    </span>
                    Retry
                </button>
            </div>
        );
    }
    return (
        <div className="loading">
            <ReactLoading type="bubbles" color="#3498db" />
        </div>
    );
};

export default Loading;
