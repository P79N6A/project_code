<?php
// 投资通接口配置文档

#商户编号
$merchantaccount='10012471228';

#商户公钥
$merchantPublicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCeU/NP1VuvjhNsV8e1un9woQFJ010P3OyGWRK3tMAg670ielp9tDa49xfwIiDuMkiqH2obGi0EaoKvjH+Vd4YrSaojeooDA3a2uXVltsICQuN0KUJWfiSQi0DVja5NF74Yx6BOGeYWd2IsMchB0vjWusXF9rJRshymweYWw/Z1GwIDAQAB';

#
#商户私钥
$merchantPrivateKey='MIICdgIBADANBgkqhkiG9w0BAQEFAASCAmAwggJcAgEAAoGBAJ5T80/VW6+OE2xXx7W6f3ChAUnTXQ/c7IZZEre0wCDrvSJ6Wn20Nrj3F/AiIO4ySKofahsaLQRqgq+Mf5V3hitJqiN6igMDdra5dWW2wgJC43QpQlZ+JJCLQNWNrk0XvhjHoE4Z5hZ3YiwxyEHS+Na6xcX2slGyHKbB5hbD9nUbAgMBAAECgYAZctj7DgpMr4ODuKBLH4z4Z4izexvMPvBtr8eIa68uG3YtIomFBwB8vorEeFfesYpofeAqNwzhVtVmriibt7iC3/gFVhg9rmrpYCiNGDbLRSmz9m8DP/K+Dbf0APqaOMi9WTDudv8yQ9c5HTSVvFU0WRc6W+QQY0CASZdHqWn7AQJBAM68woiJ6JB5FBQjLr7W0HVnoynl0Jn4M6+ybxYhLKYYz0xlsjXZtZl60Uzus3hygbpPI/QPnBRGqjE1nJx/OVsCQQDEDievttAqXHfAJxJypdkymyOSwVYjaHAYBSXZVC6BazOP+ey4zG7KheE59rInIH0wyV8BjlZeScYTyUib+r9BAkAlMrGN/8JovGBwfyQaEmzPsyYYk9FE4vPp2SFDyhROjog+Js46AkI6q26deRWxxmixLSw67dQXkd9tm0fioMGhAkEAsLup3uZHhMhSUu9l29/RiaL8UGDki4qr8ZtCYUVXnubKVnGPiY8QGJTTUXMnacoJ0J7WfAqZpOmQG2oJgEJrgQJAYCww55Sm0wgoea3Xgyy8QWFJ0eZZeuGJhinzwOXA8wwWbW7woR1QPmuff3lbEAfxdING5ce/KHy40oIfEkvczA==';

#
#易宝公玥
$yeepayPublicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCYFsG2IzliRHysgdZjGTPGyRbzZLAjVrjkdcz0uM2MzaG08tWqATRkhr8ecsSesjsWfX/nfQ/jPy3BKMIvAVErw5K21AH9rG8+t4WocQ1EmM88MYJX4PVq0Rp8b3tKJ12PDk6qZTeJiwFsCt5EW4wigYhAi3C9XWz8qznFA/nf+wIDAQAB';

return [
    'merchantaccount'=>$merchantaccount,
    'merchantPublicKey'=>$merchantPublicKey,
    'merchantPrivateKey'=>$merchantPrivateKey,
    'yeepayPublicKey'=>$yeepayPublicKey,
];
