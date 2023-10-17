const baseUrl = "http://127.0.0.1:8002/"

const instance = axios.create({
        baseURL: 'http://127.0.0.1:8002/',

        // `transformRequest` 允许在向服务器发送前，修改请求数据
        // 它只能用于 'PUT', 'POST' 和 'PATCH' 这几个请求方法
        // 数组中最后一个函数必须返回一个字符串， 一个Buffer实例，ArrayBuffer，FormData，或 Stream
        // 你可以修改请求头。
        transformRequest: [function (data, headers) {
            // 对发送的 data 进行任意转换处理

            return data;
        }],

        // `transformResponse` 在传递给 then/catch 前，允许修改响应数据
        transformResponse: [function (data) {
            // 对接收的 data 进行任意转换处理

            return data;
        }],

        // 自定义请求头
        headers: {
            "Accept": "application/json, text/javascript, */*; q=0.01",
            "Accept-Language": "zh-CN,zh;q=0.9",
            "Cache-Control": "no-cache",
            "Pragma": "no-cache",
            "X-Requested-With": "XMLHttpRequest",
        },
        // `timeout` 指定请求超时的毫秒数。
        // 如果请求时间超过 `timeout` 的值，则请求会被中断
        timeout: 1000, // 默认值是 `0` (永不超时)

        // `responseType` 表示浏览器将要响应的数据类型
        // 选项包括: 'arraybuffer', 'document', 'json', 'text', 'stream'
        // 浏览器专属：'blob'
        responseType:
            'json', // 默认值

        // `responseEncoding` 表示用于解码响应的编码 (Node.js 专属)
        // 注意：忽略 `responseType` 的值为 'stream'，或者是客户端请求
        // Note: Ignored for `responseType` of 'stream' or client-side requests
        responseEncoding:
            'utf8', // 默认值

        // `xsrfCookieName` 是 xsrf token 的值，被用作 cookie 的名称
        xsrfCookieName:
            'XSRF-TOKEN', // 默认值

        // `xsrfHeaderName` 是带有 xsrf token 值的http 请求头名称
        xsrfHeaderName:
            'X-XSRF-TOKEN', // 默认值

        // `onUploadProgress` 允许为上传处理进度事件
        // 浏览器专属
        onUploadProgress:

            function (progressEvent) {
                // 处理原生进度事件
            }

        ,

// `onDownloadProgress` 允许为下载处理进度事件
// 浏览器专属
        onDownloadProgress: function (progressEvent) {
            // 处理原生进度事件
        }
        ,

// `paramsSerializer`是可选方法，主要用于序列化`params`
// (e.g. https://www.npmjs.com/package/qs, http://api.jquery.com/jquery.param/)
// paramsSerializer: function (params) {
//     return Qs.stringify(params, {arrayFormat: 'brackets'})
// },


// `withCredentials` 表示跨域请求时是否需要使用凭证
// withCredentials: false, // default

// `adapter` 允许自定义处理请求，这使测试更加容易。
// 返回一个 promise 并提供一个有效的响应 （参见 lib/adapters/README.md）。
// adapter: function (config) {
//     /* ... */
// },

// http基本验证
// auth: {
//     username: 'janedoe',
//     password: 's00pers3cret'
// },


// `maxContentLength` 定义了node.js中允许的HTTP响应内容的最大字节数
// maxContentLength: 2000,

// `maxBodyLength`（仅Node）定义允许的http请求内容的最大字节数
// maxBodyLength: 2000,

// `validateStatus` 定义了对于给定的 HTTP状态码是 resolve 还是 reject promise。
// 如果 `validateStatus` 返回 `true` (或者设置为 `null` 或 `undefined`)，
// 则promise 将会 resolved，否则是 rejected。
// validateStatus: function (status) {
//     return status >= 200 && status < 300; // 默认值
// },

// `maxRedirects` 定义了在node.js中要遵循的最大重定向数。
// 如果设置为0，则不会进行重定向
// maxRedirects: 5, // 默认值

// `socketPath` 定义了在node.js中使用的UNIX套接字。
// e.g. '/var/run/docker.sock' 发送请求到 docker 守护进程。
// 只能指定 `socketPath` 或 `proxy` 。
// 若都指定，这使用 `socketPath` 。
// socketPath: null, // default


// `proxy` 定义了代理服务器的主机名，端口和协议。
// 您可以使用常规的`http_proxy` 和 `https_proxy` 环境变量。
// 使用 `false` 可以禁用代理功能，同时环境变量也会被忽略。
// `auth`表示应使用HTTP Basic auth连接到代理，并且提供凭据。
// 这将设置一个 `Proxy-Authorization` 请求头，它会覆盖 `headers` 中已存在的自定义 `Proxy-Authorization` 请求头。
// 如果代理服务器使用 HTTPS，则必须设置 protocol 为`https`
// proxy: {
//     protocol: 'https',
//     host: '127.0.0.1',
//     port: 9000,
//     auth: {
//         username: 'mikeymike',
//         password: 'rapunz3l'
//     }
// },

// `decompress` indicates whether or not the response body should be decompressed
// automatically. If set to `true` will also remove the 'content-encoding' header
// from the responses objects of all decompressed responses
// - Node only (XHR cannot turn off decompression)
// `decompress`表示是否需要解压响应体自动。
// 如果设置为`true`， ` content-encoding `头也会被移除
// 从所有解压缩响应的responses对象中获取
// 仅支持Node (XHR无法关闭解压)

// decompress: true // 默认值
    })
;

const getA = (data) => {
    instance.get("app/index.php?i=3&c=entry&do=yb_getNewsZujiData&m=we7_bybook&id=114")
        .then((response) => {
            console.log(response.data);
            console.log(response.status);
            console.log(response.statusText);
            console.log(response.headers);
            console.log(response.config);
        })
}


(() => {
    getA()
})()
