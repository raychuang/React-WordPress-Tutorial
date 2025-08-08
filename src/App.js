import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Button, Panel, PanelBody, TextControl } from '@wordpress/components';

function App() {
    const [message, setMessage] = useState('');
    const [isLoading, setIsLoading] = useState(true);

    // 1. 组件加载时，获取初始数据
    useEffect(() => {
        apiFetch({ path: '/my-react-settings-page/v1/settings' })
            .then((settings) => {
                setMessage(settings.message);
                setIsLoading(false);
            });
    }, []);

    // 2. 点击保存按钮时，发送数据
    const handleSave = () => {
        setIsLoading(true);
        apiFetch({
            path: '/my-react-settings-page/v1/settings',
            method: 'POST',
            data: { message: message },
        }).then((response) => {
            console.log('保存成功!', response);
            setIsLoading(false);
        });
    };

    if (isLoading) {
        return <div>正在加载...</div>;
    }

    return (
        <div className="wrap">
            <h1>公告栏设置</h1>
            <Panel>
                <PanelBody title="公告内容设置">
                    <TextControl
                        label="公告消息"
                        value={message}
                        onChange={(newMessage) => setMessage(newMessage)}
                        help="输入你想在网站顶部显示的公告内容。"
                    />
                    <Button variant="primary" onClick={handleSave} isBusy={isLoading}>
                        保存设置
                    </Button>
                </PanelBody>
            </Panel>
        </div>
    );
}

export default App;