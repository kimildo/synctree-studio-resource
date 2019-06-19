import React from 'react';

const DownloadSample = ({ type }) => {
    switch (type) {
        case 'json':
            let dataStr = `data:text/json;charset=utf-8,${encodeURIComponent(
                JSON.stringify([
                    {
                        key: 'test',
                        var_type: 'integer',
                        required_flag: '',
                        desc: 'test',
                    },
                    {
                        key: '',
                        var_type: '',
                        required_flag: 'TRUE',
                        desc: 'test',
                    },
                    {
                        key: 'test3',
                        var_type: 'asd',
                        required_flag: 'asd',
                        desc: '',
                    },
                ])
            )}`;
            return (
                <a
                    className={'btn btn-default '}
                    href={dataStr}
                    download="sample.json">
                    {`Sample Source( ${type.toUpperCase()} )`}
                </a>
            );

        default:
            return (
                <a
                    className={'btn btn-default '}
                    href={`/htdocs/download/sample_params/sample.${type}`}
                    rel="noopener noreferrer"
                    target="_blank">
                    {`Sample Source( ${type.toUpperCase()} )`}
                </a>
            );
    }
};

export default DownloadSample;
