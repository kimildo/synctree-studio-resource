import React, { Component } from 'react';
import _ from 'lodash';
import Util from '../../../library/utils/Util';
import { dataType } from '../../../library/constant/CommonConst';

import DownloadSample from './DownloadSample';

class FileUploadParams extends Component {
    state = { ext: 'json' };
    uploadFile = e => {
        const fileReader = new FileReader();
        const f = e.target.files[0],
            ext = _.last(f.name.split('.'));

        if (f.type !== 'application/json' && ext !== 'csv' && ext !== 'tsv') {
            Util.showSmallBox(
                'error_message',
                1000,
                '허용된 타입의 파일이 아닙니다.'
            );
            return false;
        }
        fileReader.readAsText(f);
        e.target.value = '';
        fileReader.onloadend = () => {
            let res = {};
            if (ext === 'csv' || ext === 'tsv') {
                res = Util.parseCsvMethod(
                    fileReader.result,
                    ext === 'csv' ? ',' : '\t'
                );
            } else {
                res = JSON.parse(fileReader.result);
            }
            Util.confirmMessage(
                'Confirm',
                '업로드된 데이터를 덮어쓰겠습니까?',
                '[하단삽입][덮어쓰기]',
                '덮어쓰기'
            )
                .then(() => {
                    this.props.updateParams(res, 'replace');
                })
                .catch(() => {
                    this.props.updateParams(res, 'push');
                });
        };
    };
    export = e => {
        e.preventDefault();
        const { ext } = this.state,
            { params, type } = this.props,
            downloadAnchorNode = document.createElement('a');
        let saveData = [],
            dataStr = '';

        switch (ext) {
            case 'json':
                saveData = params.map(p => {
                    return {
                        key: p[`${type}_key`],
                        var_type: dataType[p[`${type}_var_type`]],
                        required_flag:
                            p[`${type}_required_flag`] === 1 ? 'TRUE' : 'FALSE',
                        desc: p[`${type}_desc`],
                    };
                });
                dataStr = `data:text/json;charset=utf-8,${encodeURIComponent(
                    JSON.stringify(saveData)
                )}`;

                break;
            case 'csv':
            case 'tsv':
                let t = ext === 'csv' ? 'csv' : 'tab-separated-values',
                    j = ext === 'csv' ? ',' : '\t';

                saveData = [['key', 'var type', 'description', 'required']];
                _.forEach(params, p => {
                    saveData.push([
                        p[`${type}_key`],
                        dataType[p[`${type}_var_type`]],
                        p[`${type}_desc`],
                        p[`${type}_required_flag`] === 1 ? 'TRUE' : 'FALSE',
                    ]);
                });

                dataStr = `data:text/${t};charset=utf-8,${encodeURIComponent(
                    saveData.map(e => e.join(j)).join('\n')
                )}`;

                break;

            default:
                return;
        }
        downloadAnchorNode.setAttribute('href', dataStr);
        downloadAnchorNode.setAttribute('download', `exported_sample.${ext}`);
        document.body.appendChild(downloadAnchorNode); // required for firefox
        downloadAnchorNode.click();
        downloadAnchorNode.remove();
    };
    onChangeType = e => {
        this.setState({ ext: e.target.value });
    };
    render() {
        const { ext } = this.state;
        return (
            <footer className="file-upload-btns">
                <div className="pull-right">
                    <button
                        className={'btn btn-danger'}
                        type="button"
                        onClick={this.export}>
                        Export ( {ext.toUpperCase()} )
                    </button>

                    <label className={'btn btn-primary'}>
                        <input
                            className="hide"
                            accept=".json,.csv,.tsv"
                            type="file"
                            onChange={this.uploadFile}
                        />
                        Import
                    </label>
                    <DownloadSample type={ext} />

                    <select onChange={this.onChangeType} value={ext}>
                        <option value="json">JSON</option>
                        <option value="csv">CSV</option>
                        <option value="tsv">TSV</option>
                    </select>
                    <i />
                </div>
            </footer>
        );
    }
}
export default FileUploadParams;
