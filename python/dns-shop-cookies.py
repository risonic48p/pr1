from selenium import webdriver
import time
import undetected_chromedriver as uc
import json

options = webdriver.ChromeOptions()
options.page_load_strategy = 'normal'
options.add_argument('--window-size=1200,1100')
options.add_argument("--remote-allow-origins=*")
options.add_argument("--password-store=basic")
options.add_argument("--no-sandbox")
options.add_argument("--disable-setuid-sandbox")

driver = uc.Chrome(options=options, version_main = 146)

driver.get("https://www.dns-shop.ru/brand/onkron/")
time.sleep(1)
cookies = driver.get_cookies()
print(json.dumps(cookies, indent=4))
driver.quit()
