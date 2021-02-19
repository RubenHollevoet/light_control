<?php


namespace App\Services;


use App\Entity\Device;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Socket\Raw\Factory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Yeelight\Bulb\Bulb;
use Yeelight\Bulb\BulbProperties;
use Yeelight\Bulb\Response;
use Yeelight\YeelightClient;
use Yeelight\YeelightRawClient;

class YeelightService
{
    private $em;
    private $yeelightClient;
    private $session;
    private $socket;
    
    
    private $fp;

    /**
     * YeelightService constructor.
     */
    public function __construct(EntityManagerInterface $em, YeelightClient $yeelightClient, SessionInterface $session)
    {
        $this->session = $session;
        $this->em = $em;
        $this->yeelightClient = $yeelightClient;

        $socketFactory = new Factory();
        $this->socket = $socketFactory->createUdp4();
    }

    public function scanForYeelights() {
        try {
            $foundYeelights = $this->yeelightClient->search();
        }
        catch (\Exception $e) {
            $this->session->getFlashBag()->add('danger', 'Error fetching yeelights: '.$e->getMessage());

            return false;
        }

        foreach ($foundYeelights as $yeelight) {

            if(!$device = $this->em->getRepository(Device::class)->findOneBy(['deviceId' => $yeelight->getId()]))
            {
                $device = new Device();
                $device->setDeviceId($yeelight->getId());
                $device->setBrand(Device::BRAND_YEELIGHT);
                $device->setName('Yeelight');
                $device->setSort(0);
                $this->em->persist($device);
            }

            $device->setIP($yeelight->getIp());
            $device->setLastScan(new \DateTime());
        }
        $this->em->flush();
    }

    public function processEvent(string $target, string $method, string $params, int $responseId) {
        $params = $params === '0' ? [] : explode(',', $params);
        $params = str_replace('-', ',', $params);
        $params = str_replace('.', ',', $params);

        if(strpos($target, 't') === 0) {
            $yeeResp = [];
            /** @var Tag $tag */
            if(null === $tag = $this->em->getRepository(Tag::class)->find(substr($target, 1))) {
                throw new NotFoundHttpException('Unknown tag for id '.substr($target, 1));
            }
            foreach ($tag->getDevices() as $device) {
                if($device->getBrand() === Device::BRAND_YEELIGHT) {
                    $yeeResp[] = $this->execute($device, $method, $params, $responseId);
                }
            }
        }
        else {
            /** @var Device $device */
            if(null === $device = $this->em->getRepository(Device::class)->findOneBy(['id' => $target, 'brand' => Device::BRAND_YEELIGHT])) {
                throw new NotFoundHttpException('No Yeelight registered with this ID');
            }
            $yeeResp = $this->execute($device, $method, $params, $responseId);
        }

        return new JsonResponse($yeeResp);
    }

    public function execute(Device $device, string $method, array $params, int $responseId = 0) {
        $errno = '';
        $errstr = '';

        try {
            if(! $fp = fsockopen($device->getIP(), 55443, $errno, $errstr, 3)) {
                return false;
            }
        }
        catch (\ErrorException $e) {
            return '2 sec timeout connecting to '.$device->getIP() . ' - ' . $device->getName();
        }
        catch (\Exception $e) {
            return 'error connecting to '.$device->getIP();
        }

        //convert string parameters to number if needed
        foreach ($params as $key => $param){
            if(is_numeric($param)) {
                $params[$key] = (int) $param;
            }
        }

        stream_set_blocking($fp, false);

        $dataStr = json_encode([
            'id' => $responseId,
            'method' => $method,
            'params' => $params
        ]);

        fwrite($fp, $dataStr . "\r\n");
        fflush($fp);

        while(!$resultStr = fgets($fp)) {
            usleep(1000);
        }
        usleep(10 * 1000); //initial value = 100 * 1000

        return json_decode($resultStr);
    }
}
